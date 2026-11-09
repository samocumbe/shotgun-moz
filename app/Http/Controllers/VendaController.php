<?php

/**
 * ============================================================
 * CONTROLLER: VendaController
 * ============================================================
 * 
 * Gerencia todas as operações relacionadas a vendas.
 * 
 * Regras de Acesso (RBAC):
 * - Admin: Vê todas as vendas.
 * - Operador: Vê as vendas dos eventos da sua organização.
 * - Promoter: Vê APENAS as vendas que ele próprio realizou.
 * - Cliente: Sem acesso.
 * 
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Evento;
use App\Models\Pedido;
use App\Models\Participante;
use App\Models\CategoriaIngresso;
use App\Models\Ingresso;
use App\Models\ItemPedido;
use App\Models\Pagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VendaController extends Controller
{
    /**
     * ============================================================
     * INDEX: Lista todas as vendas com filtros
     * ============================================================
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Iniciar a consulta com os relacionamentos necessários
        $query = Venda::with(['evento', 'user', 'participante', 'pedido']);
        
        // ============================================
        // FILTRO DE SEGURANÇA (RBAC)
        // ============================================
        if ($user->isPromoter()) {
            // Promoter só vê as suas próprias vendas
            $query->where('user_id', $user->id);
        } elseif ($user->isOperador()) {
            // Operador vê as vendas da sua organização
            $query->whereHas('evento', function($q) use ($user) {
                $q->where('organizacao_id', $user->organizacao_id);
            });
        } elseif (!$user->isAdmin()) {
            // Se for cliente (ou outro), bloqueia o acesso
            abort(403, 'Não tens permissão para acessar as vendas.');
        }
        
        // ============================================
        // APLICAÇÃO DOS FILTROS NORMAIS
        // ============================================
        if ($request->filled('evento_id')) {
            $query->where('evento_id', $request->evento_id);
        }
        
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('participante', function($q) use ($search) {
                $q->where('nome_completo', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefone', 'like', "%{$search}%");
            });
        }
        
        $vendas = $query->orderBy('data_venda', 'desc')->paginate(15);
        
        // Buscar eventos para o filtro (apenas publicados e da organização se não for admin)
        $queryEventos = Evento::where('status', 'publicado');
        if (!$user->isAdmin()) {
            $queryEventos->where('organizacao_id', $user->organizacao_id);
        }
        $eventos = $queryEventos->get();
        
        return view('admin.vendas.index', compact('vendas', 'eventos'));
    }

    /**
     * ============================================================
     * CREATE: Mostra o formulário para criar uma nova venda
     * ============================================================
     */
    public function create()
    {
        $user = Auth::user();
        
        // Buscar eventos publicados (filtrados por organização)
        $queryEventos = Evento::where('status', 'publicado');
        if (!$user->isAdmin()) {
            $queryEventos->where('organizacao_id', $user->organizacao_id);
        }
        $eventos = $queryEventos->orderBy('data_inicio', 'asc')->get();
        
        $categorias = collect();
        
        return view('admin.vendas.create', compact('eventos', 'categorias'));
    }

    /**
     * ============================================================
     * STORE: Salva uma nova venda no banco de dados
     * ============================================================
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'evento_id' => 'required|exists:eventos,id',
            'categoria_ingresso_id' => 'required|exists:categorias_ingressos,id',
            'participante_nome' => 'required|string|max:255',
            'participante_email' => 'required|email|max:255',
            'participante_telefone' => 'required|string|max:20',
            'quantidade' => 'required|integer|min:1|max:10',
            'metodo_pagamento' => ['required', Rule::in(['dinheiro', 'mpesa', 'emola', 'cartao'])],
            'tipo_venda' => ['required', Rule::in(['presencial', 'online', 'promoter'])],
            'observacoes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $evento = Evento::find($request->evento_id);
        $categoria = CategoriaIngresso::find($request->categoria_ingresso_id);

        if (!$categoria->temIngressosDisponiveis()) {
            return redirect()->back()->with('error', 'Não há ingressos disponíveis nesta categoria')->withInput();
        }

        if (!$evento->temIngressosDisponiveis()) {
            return redirect()->back()->with('error', 'Evento está lotado')->withInput();
        }

        $participante = Participante::where('email', $request->participante_email)->first();
        
        if (!$participante) {
            $participante = Participante::create([
                'uuid' => (string) Str::uuid(),
                'nome_completo' => $request->participante_nome,
                'email' => $request->participante_email,
                'telefone' => $request->participante_telefone,
            ]);

            $user = \App\Models\User::where('email', $request->participante_email)->first();
            if ($user) {
                $participante->user_id = $user->id;
                $participante->save();
            }
        }

        $subtotal = $categoria->preco * $request->quantidade;
        $pedido = Pedido::create([
            'uuid' => (string) Str::uuid(),
            'evento_id' => $evento->id,
            'participante_id' => $participante->id,
            'user_id' => auth()->id(),
            'numero_pedido' => 'PED-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'subtotal' => $subtotal,
            'desconto' => 0,
            'total' => $subtotal,
            'status' => 'pago',
            'tipo_venda' => $request->tipo_venda,
            'data_pagamento' => now(),
        ]);

        $ingressosCriados = [];
        
        for ($i = 0; $i < $request->quantidade; $i++) {
            $ingresso = Ingresso::create([
                'uuid' => (string) Str::uuid(),
                'qr_code' => 'EVT' . date('Y') . '.' . strtoupper(Str::random(6)) . '.' . strtoupper(Str::random(8)),
                'evento_id' => $evento->id,
                'categoria_ingresso_id' => $categoria->id,
                'participante_id' => $participante->id,
                'preco_pago' => $categoria->preco,
                'status' => 'pago',
                'data_compra' => now(),
            ]);

            ItemPedido::create([
                'pedido_id' => $pedido->id,
                'categoria_ingresso_id' => $categoria->id,
                'ingresso_id' => $ingresso->id,
                'quantidade' => 1,
                'preco_unitario' => $categoria->preco,
                'subtotal' => $categoria->preco,
            ]);

            $categoria->increment('quantidade_vendida');
            $evento->increment('ingressos_vendidos');

            $ingressosCriados[] = $ingresso;
            $this->adicionarIngressoACarteira($ingresso, $participante);
        }

        Venda::create([
            'uuid' => (string) Str::uuid(),
            'evento_id' => $evento->id,
            'user_id' => auth()->id(),
            'pedido_id' => $pedido->id,
            'participante_id' => $participante->id,
            'tipo' => $request->tipo_venda,
            'valor_total' => $subtotal,
            'status' => 'confirmada',
            'data_venda' => now(),
            'observacoes' => $request->observacoes,
        ]);

        Pagamento::create([
            'uuid' => (string) Str::uuid(),
            'pedido_id' => $pedido->id,
            'valor' => $subtotal,
            'metodo' => $request->metodo_pagamento,
            'status' => 'confirmado',
            'data_confirmacao' => now(),
        ]);

        return redirect()->route('vendas.index')
            ->with('success', 'Venda realizada com sucesso! ' . $request->quantidade . ' ingresso(s) emitido(s).');
    }

    /**
     * ============================================================
     * SHOW: Exibe os detalhes de uma venda específica
     * ============================================================
     */
    public function show($id)
    {
        $venda = Venda::with([
            'evento', 'user', 'participante', 'pedido.itens.ingresso', 'pedido.itens.categoria', 'pedido.pagamentos'
        ])->findOrFail($id);

        $user = Auth::user();

        // Segurança: Promoter só vê as suas
        if ($user->isPromoter() && $venda->user_id !== $user->id) {
            abort(403, 'Não tens permissão para ver esta venda.');
        }
        
        // Segurança: Operador só vê da sua organização
        if ($user->isOperador() && $venda->evento->organizacao_id !== $user->organizacao_id) {
            abort(403, 'Não tens permissão para ver esta venda.');
        }

        return view('admin.vendas.show', compact('venda'));
    }

    /**
     * ============================================================
     * CANCELAR: Cancela uma venda existente
     * ============================================================
     */
    public function cancelar($id)
    {
        $venda = Venda::findOrFail($id);
        $user = Auth::user();

        // Segurança: Promoter só cancela as suas
        if ($user->isPromoter() && $venda->user_id !== $user->id) {
            abort(403, 'Não tens permissão para cancelar esta venda.');
        }

        if ($venda->status === 'cancelada') {
            return redirect()->back()->with('error', 'Venda já está cancelada');
        }

        $pedido = $venda->pedido;
        if ($pedido) {
            $pedido->status = 'cancelado';
            $pedido->save();

            foreach ($pedido->itens as $item) {
                if ($item->ingresso) {
                    $item->ingresso->status = 'cancelado';
                    $item->ingresso->save();
                    $this->removerIngressoDaCarteira($item->ingresso);
                }
            }
        }

        $venda->status = 'cancelada';
        $venda->save();

        return redirect()->route('vendas.index')->with('success', 'Venda cancelada com sucesso!');
    }

    /**
     * ============================================================
     * MÉTODOS AUXILIARES E AJAX
     * ============================================================
     */

    private function adicionarIngressoACarteira($ingresso, $participante)
    {
        if (!$participante->user_id) return;

        try {
            $exists = \App\Models\CarteiraIngresso::where('ingresso_id', $ingresso->id)->where('user_id', $participante->user_id)->exists();
            if (!$exists) {
                \App\Models\CarteiraIngresso::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $participante->user_id,
                    'ingresso_id' => $ingresso->id,
                    'status' => \App\Models\CarteiraIngresso::STATUS_ATIVO,
                    'data_ativacao' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar ingresso à carteira: ' . $e->getMessage());
        }
    }

    private function removerIngressoDaCarteira($ingresso)
    {
        try {
            $carteiraItems = \App\Models\CarteiraIngresso::where('ingresso_id', $ingresso->id)->get();
            foreach ($carteiraItems as $item) {
                if ($item->isAtivo()) $item->delete();
            }
        } catch (\Exception $e) {
            Log::error('Erro ao remover ingresso da carteira: ' . $e->getMessage());
        }
    }

    public function getCategorias($eventoId)
    {
        $categorias = CategoriaIngresso::where('evento_id', $eventoId)->where('quantidade_total', '>', 0)->get()->map(function($categoria) {
            $disponiveis = $categoria->quantidade_total - $categoria->quantidade_vendida;
            return ['id' => $categoria->id, 'nome' => $categoria->nome, 'preco' => $categoria->preco, 'disponiveis' => max(0, $disponiveis)];
        })->filter(fn($c) => $c['disponiveis'] > 0)->values();

        return response()->json($categorias);
    }

    public function estatisticas($eventoId)
    {
        $evento = Evento::findOrFail($eventoId);
        $stats = [
            'total_vendas' => Venda::where('evento_id', $eventoId)->count(),
            'total_ingressos' => $evento->ingressos_vendidos,
            'total_receita' => Venda::where('evento_id', $eventoId)->sum('valor_total'),
            'vendas_por_tipo' => Venda::where('evento_id', $eventoId)->selectRaw('tipo, COUNT(*) as total')->groupBy('tipo')->get(),
            'vendas_hoje' => Venda::where('evento_id', $eventoId)->whereDate('data_venda', now()->toDateString())->count(),
            'receita_hoje' => Venda::where('evento_id', $eventoId)->whereDate('data_venda', now()->toDateString())->sum('valor_total'),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function recibo($id)
    {
        try {
            $venda = Venda::with(['evento', 'participante', 'pedido.itens.ingresso', 'pedido.itens.categoria', 'pedido.pagamentos', 'user'])->findOrFail($id);

            $qrCode = null;
            if ($venda->pedido && $venda->pedido->itens->count() > 0) {
                $primeiroIngresso = $venda->pedido->itens->first()->ingresso;
                if ($primeiroIngresso) {
                    $qrCode = base64_encode(QrCode::size(150)->format('png')->generate($primeiroIngresso->qr_code));
                }
            }

            $pdf = Pdf::loadView('admin.vendas.recibo-pdf', ['venda' => $venda, 'qrCode' => $qrCode]);
            $pdf->setPaper('A4', 'portrait');
            return $pdf->download('recibo-venda-' . $venda->id . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            $venda = Venda::with(['evento', 'participante', 'pedido.itens.ingresso', 'pedido.itens.categoria', 'pedido.pagamentos'])->findOrFail($id);
            return view('admin.vendas.recibo', compact('venda'));
        }
    }

    public function ingressosJson($id)
    {
        try {
            $venda = Venda::with(['pedido.itens.ingresso', 'pedido.itens.categoria'])->findOrFail($id);
            if (!$venda->pedido || $venda->pedido->itens->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Esta venda não possui ingressos.'], 404);
            }
            
            $ingressos = $venda->pedido->itens->map(function($item) {
                $ingresso = $item->ingresso;
                if (!$ingresso) return null;
                $qrCodeBase64 = base64_encode(QrCode::size(200)->format('png')->generate($ingresso->qr_code));
                
                return [
                    'id' => $ingresso->id, 'qr_code' => $ingresso->qr_code, 'categoria' => $item->categoria->nome ?? 'N/A',
                    'qr_code_url' => route('ingressos.qrcode', ['eventoId' => $ingresso->evento_id, 'id' => $ingresso->id]),
                    'qr_code_base64' => $qrCodeBase64, 'status' => $ingresso->status, 'preco' => number_format($item->preco_unitario, 2, ',', '.') . ' MZN'
                ];
            })->filter()->values();
            
            return response()->json(['success' => true, 'ingressos' => $ingressos, 'total' => $ingressos->count()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}