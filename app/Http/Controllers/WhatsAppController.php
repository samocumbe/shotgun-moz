<?php

/**
 * ============================================================
 * CONTROLLER: WhatsAppController - SHOTGUN-MOZ
 * ============================================================
 *
 * Gerencia o envio e listagem de mensagens WhatsApp.
 *
 * Rotas:
 * - index      → Listagem com filtros e estatísticas
 * - create     → Formulário de envio
 * - store      → Processa o envio
 * - show       → Detalhes de uma mensagem
 * - reenviar   → Reenvia mensagem com falha (JSON)
 *
 * Multi-Organizador:
 * - Organizadores veem apenas mensagens da sua organização
 * - Admin vê tudo
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Models\Evento;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Lista todas as mensagens WhatsApp
     */
    public function index(Request $request)
    {
        $query = WhatsAppMessage::with(['user', 'organizacao', 'evento']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('telefone')) {
            $query->where('telefone', 'like', "%{$request->telefone}%");
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        // Multi-Organizador
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id) {
            $query->where('organizacao_id', auth()->user()->organizacao_id);
        }

        $whatsapp = $query->orderBy('created_at', 'desc')->paginate(30);

        // Estatísticas
        $statsQuery = WhatsAppMessage::query();
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id) {
            $statsQuery->where('organizacao_id', auth()->user()->organizacao_id);
        }

        $estatisticas = [
            'total'      => (clone $statsQuery)->count(),
            'pendentes'  => (clone $statsQuery)->where('status', 'pendente')->count(),
            'enviados'   => (clone $statsQuery)->where('status', 'enviado')->count(),
            'entregues'  => (clone $statsQuery)->where('status', 'entregue')->count(),
            'lidos'      => (clone $statsQuery)->where('status', 'lido')->count(),
            'falhas'     => (clone $statsQuery)->where('status', 'falha')->count(),
            'hoje'       => (clone $statsQuery)->whereDate('created_at', today())->count(),
        ];

        return view('admin.whatsapp.index', compact('whatsapp', 'estatisticas'));
    }

    /**
     * Formulário de envio
     */
    public function create()
    {
        $query = Evento::query();
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id) {
            $query->where('organizacao_id', auth()->user()->organizacao_id);
        }
        $eventos = $query->orderBy('data_inicio', 'desc')->get();

        // Templates ativos
        $templates = WhatsAppTemplate::where('is_active', true)
            ->orderBy('nome')
            ->get();

        return view('admin.whatsapp.create', compact('eventos', 'templates'));
    }

    /**
     * Envia uma mensagem WhatsApp
     */
    public function store(Request $request)
    {
        $request->validate([
            'telefone'    => 'required|string|max:20',
            'mensagem'    => 'required|string|max:4096',
            'evento_id'   => 'nullable|exists:eventos,id',
            'template_id' => 'nullable|exists:whatsapp_templates,id',
        ]);

        $resultado = $this->whatsappService->enviarWhatsApp(
            $request->telefone,
            $request->mensagem,
            [
                'evento_id'   => $request->evento_id,
                'template_id' => $request->template_id,
            ]
        );

        if ($resultado['success']) {
            return redirect()->route('whatsapp.index')
                ->with('success', $resultado['message']);
        }

        return redirect()->back()
            ->with('error', $resultado['message'])
            ->withInput();
    }

    /**
     * Detalhes de uma mensagem (view HTML)
     */
    public function show($id)
    {
        $whatsapp = WhatsAppMessage::with(['user', 'organizacao', 'evento'])
            ->findOrFail($id);

        if (!auth()->user()->isAdmin()
            && $whatsapp->organizacao_id !== auth()->user()->organizacao_id) {
            abort(403, 'Sem permissão para ver esta mensagem.');
        }

        return view('admin.whatsapp.show', compact('whatsapp'));
    }

    /**
     * Devolve JSON para o botão "ver mensagem" (AJAX)
     */
    public function showJson($id)
    {
        $whatsapp = WhatsAppMessage::findOrFail($id);

        if (!auth()->user()->isAdmin()
            && $whatsapp->organizacao_id !== auth()->user()->organizacao_id) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        return response()->json([
            'id'       => $whatsapp->id,
            'telefone' => $whatsapp->telefone,
            'mensagem' => $whatsapp->mensagem,
            'status'   => $whatsapp->status,
        ]);
    }

    /**
     * Reenvia uma mensagem com falha
     */
    public function reenviar($id, Request $request)
    {
        $whatsapp = WhatsAppMessage::findOrFail($id);

        if (!auth()->user()->isAdmin()
            && $whatsapp->organizacao_id !== auth()->user()->organizacao_id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Sem permissão.'], 403);
            }
            abort(403);
        }

        if (!$whatsapp->isFalha()) {
            $msg = 'Apenas mensagens com falha podem ser reenviadas.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return redirect()->back()->with('error', $msg);
        }

        $resultado = $this->whatsappService->enviarWhatsApp(
            $whatsapp->telefone,
            $whatsapp->mensagem,
            [
                'evento_id'   => $whatsapp->evento_id,
                'template_id' => $whatsapp->template_id,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json($resultado);
        }

        return $resultado['success']
            ? redirect()->route('whatsapp.index')->with('success', 'Mensagem reenviada com sucesso!')
            : redirect()->back()->with('error', $resultado['message']);
    }
}