<?php

/**
 * ============================================================
 * CONTROLLER: WhatsAppMassaController - SHOTGUN-MOZ
 * ============================================================
 *
 * Envio de WhatsApp em massa para participantes de um evento.
 * Espelha a estrutura do SmsMassaController.
 *
 * Rotas (definidas em routes/web.php dentro do prefixo whatsapp):
 * - GET  whatsapp/massa                       → index
 * - GET  whatsapp/massa/evento/{eventoId}     → criar
 * - POST whatsapp/massa/preview               → preview
 * - POST whatsapp/massa/enviar                → enviar
 * - GET  whatsapp/massa/resultado/{loteId}    → resultado
 *
 * Multi-Organizador:
 * - Admin vê tudo
 * - Organizador só vê eventos da sua organização
 *
 * Variáveis suportadas no renderizarMensagem():
 * - {nome}       / {{nome}}
 * - {evento}     / {{evento}}
 * - {data}       / {{data}}
 * - {local}      / {{local}}
 * - {quantidade} / {{quantidade}}
 * - {valor}      / {{valor}}     (alias: {total} / {{total}})
 *
 * NOTA IMPORTANTE SOBRE O BUG DAS VARIÁVEIS:
 * O `criar()` envia para a view duas strings já formatadas em HTML:
 *   - $variaveisHint      → lista de <code>{nome}</code>, <code>{evento}</code>, ...
 *   - $variaveisHintInfo  → texto de ajuda
 *
 * Isto evita que a view tenha que escrever chaves duplas no código,
 * o que provocava erros de ParseError no Blade com PHP 8.5 / Laravel 11.
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Venda;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Jobs\EnviarWhatsAppJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppMassaController extends Controller
{
    // ============================================================
    // 1. PÁGINA INICIAL — ESCOLHER EVENTO
    // ============================================================

    /**
     * Lista os eventos disponíveis para envio em massa.
     * Só mostra eventos 'publicado' ou 'encerrado' (exclui rascunhos).
     */
    public function index()
    {
        $query = Evento::with(['local', 'organizacao']);

        // Multi-Organizador: só eventos da própria org
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id) {
            $query->where('organizacao_id', auth()->user()->organizacao_id);
        }

        // Só eventos publicados ou encerrados (não rascunhos)
        $query->whereIn('status', ['publicado', 'encerrado']);

        $eventos = $query->orderBy('data_inicio', 'desc')->paginate(12);

        return view('admin.whatsapp.massa.index', compact('eventos'));
    }

    // ============================================================
    // 2. FORMULÁRIO — ESCOLHER CLIENTES + MENSAGEM
    // ============================================================

    /**
     * Formulário para escolher clientes, template e mensagem.
     *
     * Envia também os "hints de variáveis" já formatados em HTML
     * (strings PHP), para evitar escrever chaves duplas na view
     * (o que provoca ParseError em PHP 8.5 + Laravel 11).
     *
     * @param  int  $eventoId  ID do evento
     */
    public function criar($eventoId)
    {
        $evento = Evento::with('local')->findOrFail($eventoId);

        // Verificar permissão multi-organizador
        if (!auth()->user()->isAdmin()
            && $evento->organizacao_id !== auth()->user()->organizacao_id) {
            abort(403, 'Sem permissão para este evento.');
        }

        // Buscar clientes que compraram ingressos para este evento
        $clientes = $this->buscarClientesDoEvento($evento);

        // Templates WhatsApp ativos (próprios ou globais)
        $templates = WhatsAppTemplate::ativos()
            ->when(!auth()->user()->isAdmin() && auth()->user()->organizacao_id, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('organizacao_id', auth()->user()->organizacao_id)
                        ->orWhereNull('organizacao_id');
                });
            })
            ->orderBy('nome')
            ->get();

        // ------------------------------------------------------------
        // HINTS DE VARIÁVEIS (construídos em PHP)
        // ------------------------------------------------------------
        // Cada item é uma string HTML pronta a imprimir com {!! !!}.
        // Ex.: "<code>{nome}</code>"
        //
        // IMPORTANTE: usamos concatenação de strings para NUNCA ter
        // "{{" literal escrito no código PHP nem na view.
        // ------------------------------------------------------------
        $variaveisSimples = ['nome', 'evento', 'data', 'local', 'quantidade', 'valor'];

        $variaveisHint = [];
        foreach ($variaveisSimples as $v) {
            // Produz: <code>{nome}</code>
            $variaveisHint[] = '<code>' . '{' . $v . '}' . '</code>';
        }

        // Texto de ajuda (formato WhatsApp com chaves duplas)
        $variaveisHintInfo = 'Aceita também o formato com chaves duplas (WhatsApp): '
            . '<code>' . '{{nome}}' . '</code>, '
            . '<code>' . '{{quantidade}}' . '</code>, '
            . '<code>' . '{{valor}}' . '</code>.'
            . '<br>'
            . '<span class="text-danger">'
            . '<i class="bi bi-exclamation-triangle"></i> '
            . 'Escreve sempre as variáveis entre chaves — nunca valores literais.'
            . '</span>';

        return view('admin.whatsapp.massa.criar',
            compact('evento', 'clientes', 'templates', 'variaveisHint', 'variaveisHintInfo'));
    }

    // ============================================================
    // 3. PRÉ-VISUALIZAÇÃO
    // ============================================================

    /**
     * Renderiza a mensagem para cada cliente e mostra preview.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'evento_id'   => 'required|exists:eventos,id',
            'clientes'    => 'required|array|min:1',
            'clientes.*'  => 'integer|exists:participantes,id',
            'template_id' => 'nullable|exists:whatsapp_templates,id',
            'mensagem'    => 'required|string|max:4096',
        ]);

        $evento = Evento::with('local')->findOrFail($request->evento_id);

        // Permissão multi-organizador
        if (!auth()->user()->isAdmin()
            && $evento->organizacao_id !== auth()->user()->organizacao_id) {
            abort(403);
        }

        // Buscar participantes selecionados
        $participantes = $this->buscarClientesPorIds($request->clientes, $evento->id);

        // Renderizar mensagem para cada cliente
        $mensagens = [];
        foreach ($participantes as $p) {
            $mensagens[] = [
                'id'       => $p['id'],
                'nome'     => $p['nome'],
                'telefone' => $p['telefone'],
                'mensagem' => $this->renderizarMensagem(
                    $request->mensagem,
                    $p,
                    $evento
                ),
            ];
        }

        return view('admin.whatsapp.massa.preview', [
            'evento'      => $evento,
            'mensagens'   => $mensagens,
            'request'     => $request,
            'template_id' => $request->template_id,
        ]);
    }

    // ============================================================
    // 4. ENVIO EM MASSA (dispatch para a fila)
    // ============================================================

    /**
     * Coloca todas as mensagens na fila (Job) e redireciona
     * para a página de resultado.
     */
    public function enviar(Request $request)
    {
        $request->validate([
            'evento_id'            => 'required|exists:eventos,id',
            'mensagens'            => 'required|array|min:1',
            'mensagens.*.telefone' => 'required|string',
            'mensagens.*.mensagem' => 'required|string',
            'template_id'          => 'nullable|exists:whatsapp_templates,id',
        ]);

        $evento = Evento::findOrFail($request->evento_id);

        // Permissão
        if (!auth()->user()->isAdmin()
            && $evento->organizacao_id !== auth()->user()->organizacao_id) {
            abort(403);
        }

        // ID do lote (agrupa todas as mensagens deste envio)
        $loteId = (string) Str::uuid();
        $totalColocados = 0;

        DB::beginTransaction();
        try {
            foreach ($request->mensagens as $msg) {
                $telefone = $msg['telefone'] ?? null;
                $mensagem = $msg['mensagem'] ?? null;

                if (!$telefone || !$mensagem) {
                    continue;
                }

                // Dispatch para a fila (assíncrono)
                EnviarWhatsAppJob::dispatch($telefone, $mensagem, [
                    'evento_id'   => $evento->id,
                    'template_id' => $request->template_id,
                    'lote_id'     => $loteId,
                ]);

                $totalColocados++;
            }

            DB::commit();

            Log::info('Envio WhatsApp em massa iniciado', [
                'lote_id'   => $loteId,
                'evento_id' => $evento->id,
                'total'     => $totalColocados,
                'user_id'   => auth()->id(),
            ]);

            return redirect()
                ->route('whatsapp.massa.resultado', $loteId)
                ->with('success', "{$totalColocados} WhatsApp colocados na fila de envio!");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao iniciar envio WhatsApp em massa', [
                'erro'  => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao iniciar envio: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 5. RESULTADO
    // ============================================================

    /**
     * Mostra o progresso do lote (auto-refresh enquanto houver pendentes).
     *
     * @param  string  $loteId  UUID do lote
     */
    public function resultado($loteId)
    {
        $mensagens = WhatsAppMessage::where('referencia', $loteId)
            ->orderBy('created_at', 'desc')
            ->get();

        $estatisticas = [
            'total'     => $mensagens->count(),
            'enviados'  => $mensagens->whereIn('status', ['enviado', 'entregue', 'lido'])->count(),
            'entregues' => $mensagens->whereIn('status', ['entregue', 'lido'])->count(),
            'lidos'     => $mensagens->where('status', 'lido')->count(),
            'falhas'    => $mensagens->where('status', 'falha')->count(),
            'pendentes' => $mensagens->where('status', 'pendente')->count(),
        ];

        return view('admin.whatsapp.massa.resultado',
            compact('mensagens', 'estatisticas', 'loteId'));
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    /**
     * Busca clientes (participantes) que compraram ingressos para o evento.
     *
     * Devolve, para cada participante:
     * - id, nome, telefone
     * - total_ingressos  → nº de linhas de venda
     * - quantidade       → soma de ingressos (fallback: count)
     * - valor            → soma de valor_total (formatado em MZN)
     *
     * @return array
     */
    protected function buscarClientesDoEvento(Evento $evento): array
    {
        return Venda::with('participante')
            ->where('evento_id', $evento->id)
            ->where('status', 'confirmada')
            ->get()
            ->groupBy('participante_id')
            ->map(function ($vendas) {
                $participante = $vendas->first()->participante;

                if (!$participante || !$participante->telefone) {
                    return null;
                }

                return [
                    'id'              => $participante->id,
                    'nome'            => $participante->nome_completo,
                    'telefone'        => $participante->telefone,
                    'total_ingressos' => $vendas->count(),
                    'quantidade'      => $vendas->count(),
                    'valor'           => number_format($vendas->sum('valor_total'), 2, ',', '.'),
                ];
            })
            ->filter() // remove nulos (sem telefone)
            ->sortBy('nome')
            ->values()
            ->toArray();
    }

    /**
     * Busca clientes por lista de IDs (participantes),
     * confirmando que têm venda confirmada no evento.
     *
     * @param  array  $ids
     * @param  int    $eventoId
     * @return array
     */
    protected function buscarClientesPorIds(array $ids, int $eventoId): array
    {
        return Venda::with('participante')
            ->where('evento_id', $eventoId)
            ->where('status', 'confirmada')
            ->whereIn('participante_id', $ids)
            ->get()
            ->groupBy('participante_id')
            ->map(function ($vendas) {
                $p = $vendas->first()->participante;
                if (!$p || !$p->telefone) return null;

                return [
                    'id'              => $p->id,
                    'nome'            => $p->nome_completo,
                    'telefone'        => $p->telefone,
                    'total_ingressos' => $vendas->count(),
                    'quantidade'      => $vendas->count(),
                    'valor'           => number_format($vendas->sum('valor_total'), 2, ',', '.'),
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Renderiza a mensagem substituindo TODAS as variáveis.
     *
     * Aceita os dois formatos:
     * - SMS:       {nome}, {evento}, {data}, {local}, {quantidade}, {valor}
     * - WhatsApp:  {{nome}}, {{evento}}, {{data}}, {{local}}, {{quantidade}}, {{valor}}
     *
     * O `total` é um ALIAS de `valor` (o template WhatsApp usa `{{total}}`).
     *
     * ⚠️ IMPORTANTE: substituímos PRIMEIRO o formato com chaves duplas
     * ({{var}}) e SÓ DEPOIS o formato com chaves simples ({var}), para
     * evitar que `{{nome}}` seja parcialmente substituído por `{` + valor + `}`.
     */
    protected function renderizarMensagem(string $template, array $cliente, Evento $evento): string
    {
        $valores = [
            'nome'       => $cliente['nome'] ?? '',
            'evento'     => $evento->nome,
            'data'       => $evento->data_inicio ? $evento->data_inicio->format('d/m/Y') : '',
            'local'      => $evento->local->nome ?? '',
            'quantidade' => $cliente['quantidade'] ?? 0,
            'valor'      => $cliente['valor'] ?? '0,00',
            'total'      => $cliente['valor'] ?? '0,00',  // alias para template WhatsApp
        ];

        foreach ($valores as $chave => $valor) {
            // Formato WhatsApp (chaves duplas) — substituir PRIMEIRO
            $template = str_replace('{{' . $chave . '}}', $valor, $template);
            // Formato SMS (chaves simples) — substituir DEPOIS
            $template = str_replace('{'  . $chave . '}',  $valor, $template);
        }

        return $template;
    }
}