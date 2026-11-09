<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Venda;
use App\Models\Sms;
use App\Models\SmsTemplate;
use App\Jobs\EnviarSmsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmsMassaController extends Controller
{
    /**
     * Página inicial: escolher o evento
     */
    public function index()
    {
        $query = Evento::with(['local', 'organizacao']);

        // Multi-Organizador
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id) {
            $query->where('organizacao_id', auth()->user()->organizacao_id);
        }

        // Só eventos publicados ou encerrados (não rascunhos)
        $query->whereIn('status', ['publicado', 'encerrado']);

        $eventos = $query->orderBy('data_inicio', 'desc')->paginate(12);

        return view('admin.sms.massa.index', compact('eventos'));
    }

    /**
     * Formulário: escolher clientes + template + mensagem
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

        // Templates ativos
        $templates = SmsTemplate::ativos()
            ->when(!auth()->user()->isAdmin() && auth()->user()->organizacao_id, function ($q) {
                $q->where('organizacao_id', auth()->user()->organizacao_id)
                  ->orWhereNull('organizacao_id');
            })
            ->orderBy('nome')
            ->get();

        return view('admin.sms.massa.criar', compact('evento', 'clientes', 'templates'));
    }

    /**
     * Pré-visualizar como os SMS vão ficar
     */
    public function preview(Request $request)
    {
        $request->validate([
            'evento_id'   => 'required|exists:eventos,id',
            'clientes'    => 'required|array|min:1',
            'clientes.*'  => 'integer|exists:participantes,id',
            'template_id' => 'nullable|exists:sms_templates,id',
            'mensagem'    => 'required|string|max:480',
        ]);

        $evento = Evento::with('local')->findOrFail($request->evento_id);

        // Verificar permissão
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

        return view('admin.sms.massa.preview', [
            'evento'    => $evento,
            'mensagens' => $mensagens,
            'request'   => $request,
        ]);
    }

    /**
     * Enviar em massa (dispatch para a fila)
     */
    public function enviar(Request $request)
    {
        $request->validate([
            'evento_id'      => 'required|exists:eventos,id',
            'mensagens'      => 'required|array|min:1',
            'mensagens.*.telefone' => 'required|string',
            'mensagens.*.mensagem' => 'required|string',
        ]);

        $evento = Evento::findOrFail($request->evento_id);

        // Verificar permissão
        if (!auth()->user()->isAdmin()
            && $evento->organizacao_id !== auth()->user()->organizacao_id) {
            abort(403);
        }

        // ID do lote (agrupa todos os SMS deste envio)
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

                // Enviar via fila (assíncrono)
                EnviarSmsJob::dispatch($telefone, $mensagem, [
                    'evento_id' => $evento->id,
                    'lote_id'   => $loteId,
                ]);

                $totalColocados++;
            }

            DB::commit();

            Log::info("Envio em massa iniciado", [
                'lote_id'    => $loteId,
                'evento_id'  => $evento->id,
                'total'      => $totalColocados,
                'user_id'    => auth()->id(),
            ]);

            return redirect()
                ->route('sms.massa.resultado', $loteId)
                ->with('success', "{$totalColocados} SMS colocados na fila de envio!");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Erro ao iniciar envio em massa", [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao iniciar envio: ' . $e->getMessage());
        }
    }

    /**
     * Ver resultado do envio
     */
    public function resultado($loteId)
    {
        // Buscar SMS deste lote (usando a coluna referencia)
        $sms = Sms::where('referencia', $loteId)
            ->orderBy('created_at', 'desc')
            ->get();

        $estatisticas = [
            'total'    => $sms->count(),
            'enviados' => $sms->where('status', 'enviado')->count(),
            'falhas'   => $sms->where('status', 'falha')->count(),
            'pendentes'=> $sms->where('status', 'pendente')->count(),
        ];

        return view('admin.sms.massa.resultado', compact('sms', 'estatisticas', 'loteId'));
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    /**
     * Busca clientes (participantes) que compraram ingressos para o evento
     *
     * @return array Lista com id, nome, telefone, total_ingressos
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
        ->filter()
        ->sortBy('nome')
        ->values()
        ->toArray();
}

    /**
     * Busca clientes por lista de IDs (participantes)
     *
     * @param array $ids
     * @param int $eventoId
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
     * Renderiza a mensagem substituindo as variáveis
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
        'total'      => $cliente['valor'] ?? '0,00',  // alias
    ];

    foreach ($valores as $chave => $valor) {
        $template = str_replace('{{' . $chave . '}}', $valor, $template); // WhatsApp
        $template = str_replace('{'  . $chave . '}',  $valor, $template); // SMS
    }

    return $template;
}
}