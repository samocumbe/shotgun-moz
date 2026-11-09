<?php

namespace App\Http\Controllers;

use App\Models\Sms;
use App\Models\Evento;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Lista todos os SMS enviados
     */
    public function index(Request $request)
    {
        $query = Sms::with(['user', 'organizacao', 'evento']);

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

        $sms = $query->orderBy('created_at', 'desc')->paginate(30);

        // Estatísticas (respeitando multi-organizador)
        $statsQuery = Sms::query();
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id) {
            $statsQuery->where('organizacao_id', auth()->user()->organizacao_id);
        }

        $estatisticas = [
            'total'     => (clone $statsQuery)->count(),
            'pendentes' => (clone $statsQuery)->where('status', 'pendente')->count(),
            'enviados'  => (clone $statsQuery)->where('status', 'enviado')->count(),
            'falhas'    => (clone $statsQuery)->where('status', 'falha')->count(),
            'hoje'      => (clone $statsQuery)->whereDate('created_at', today())->count(),
        ];

        return view('admin.sms.index', compact('sms', 'estatisticas'));
    }

    /**
     * Formulário para enviar SMS
     */
    public function create()
    {
        // 🔥 CORREÇÃO: enviar eventos para o select
        $query = Evento::query();
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id) {
            $query->where('organizacao_id', auth()->user()->organizacao_id);
        }
        $eventos = $query->orderBy('data_inicio', 'desc')->get();

        return view('admin.sms.create', compact('eventos'));
    }

    /**
     * Envia um SMS
     */
    public function store(Request $request)
    {
        $request->validate([
            'telefone' => 'required|string|max:20',
            'mensagem' => 'required|string|max:160',
            'evento_id' => 'nullable|exists:eventos,id',
        ]);

        $resultado = $this->smsService->enviarSms(
            $request->telefone,
            $request->mensagem,
            ['evento_id' => $request->evento_id]
        );

        if ($resultado['success']) {
            return redirect()->route('sms.index')
                ->with('success', $resultado['message']);
        }

        return redirect()->back()
            ->with('error', $resultado['message'])
            ->withInput();
    }

    /**
     * Mostra os detalhes de um SMS (view HTML)
     */
    public function show($id)
    {
        $sms = Sms::with(['user', 'organizacao', 'evento'])->findOrFail($id);

        // Multi-Organizador: verificar permissão
        if (!auth()->user()->isAdmin() 
            && $sms->organizacao_id !== auth()->user()->organizacao_id) {
            abort(403, 'Sem permissão para ver este SMS.');
        }

        return view('admin.sms.show', compact('sms'));
    }

    /**
     * 🆕 Devolve JSON para o botão "ver mensagem" (AJAX)
     */
    public function showJson($id)
    {
        $sms = Sms::findOrFail($id);

        if (!auth()->user()->isAdmin() 
            && $sms->organizacao_id !== auth()->user()->organizacao_id) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        return response()->json([
            'id'       => $sms->id,
            'telefone' => $sms->telefone,
            'mensagem' => $sms->mensagem,
            'status'   => $sms->status,
        ]);
    }

    /**
     * Reenvia um SMS com falha (JSON para AJAX, redirect para form)
     */
    public function reenviar($id, Request $request)
    {
        $sms = Sms::findOrFail($id);

        if (!auth()->user()->isAdmin() 
            && $sms->organizacao_id !== auth()->user()->organizacao_id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Sem permissão.'], 403);
            }
            abort(403);
        }

        if (!$sms->isFalha()) {
            $msg = 'Apenas SMS com falha podem ser reenviados.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return redirect()->back()->with('error', $msg);
        }

        $resultado = $this->smsService->enviarSms(
            $sms->telefone,
            $sms->mensagem,
            ['evento_id' => $sms->evento_id]
        );

        if ($request->wantsJson()) {
            return response()->json($resultado);
        }

        return $resultado['success']
            ? redirect()->route('sms.index')->with('success', 'SMS reenviado com sucesso!')
            : redirect()->back()->with('error', $resultado['message']);
    }
}