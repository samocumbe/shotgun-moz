<?php

/**
 * ============================================================
 * CONTROLLER: IngressoController
 * ============================================================
 * 
 * REGRAS DE ACESSO:
 * - Cliente: Só vê os ingressos ligados ao email dele.
 * - Outros: Vêem todos os ingressos.
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Ingresso;
use App\Models\Evento;
use App\Models\Participante;
use App\Models\CategoriaIngresso;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class IngressoController extends Controller
{
    /**
     * Lista geral de ingressos.
     */
    public function geral(Request $request)
    {
        $query = Ingresso::with(['evento', 'categoria', 'participante']);
        
        // SEGURANÇA: Cliente só vê os seus ingressos
        if (auth()->user()->isCliente()) {
            $query->whereHas('participante', function($q) {
                $q->where('email', auth()->user()->email);
            });
        }

        if ($request->has('evento_id') && $request->evento_id) {
            $query->where('evento_id', $request->evento_id);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('qr_code', 'like', "%{$search}%")
                  ->orWhereHas('participante', function($p) use ($search) {
                      $p->where('nome_completo', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $ingressos = $query->orderBy('created_at', 'desc')->paginate(15);
        $eventos = Evento::where('status', 'publicado')->get();
        
        return view('admin.ingressos.geral', compact('ingressos', 'eventos'));
    }

    /**
     * Lista ingressos de um evento específico.
     */
    public function index(Request $request, $eventoId)
    {
        $evento = Evento::findOrFail($eventoId);
        $query = Ingresso::where('evento_id', $eventoId)->with(['categoria', 'participante']);
        
        // SEGURANÇA: Cliente só vê os seus
        if (auth()->user()->isCliente()) {
            $query->whereHas('participante', function($q) {
                $q->where('email', auth()->user()->email);
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('qr_code', 'like', "%{$search}%")
                  ->orWhereHas('participante', function($p) use ($search) {
                      $p->where('nome_completo', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $ingressos = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.ingressos.index', compact('evento', 'ingressos'));
    }

    /**
     * Venda de novo ingresso.
     */
    public function store(Request $request, $eventoId)
    {
        $evento = Evento::findOrFail($eventoId);
        
        if (!$evento->isPublicado() || $evento->isEncerrado()) {
            return redirect()->back()->with('error', 'Evento não disponível.');
        }

        $validator = Validator::make($request->all(), [
            'categoria_ingresso_id' => 'required|exists:categorias_ingressos,id',
            'participante_nome' => 'required|string|max:255',
            'participante_email' => 'required|email|max:255',
            'participante_telefone' => 'required|string|max:20',
            'participante_nuit' => 'nullable|string|max:20',
            'quantidade' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();

        $categoria = CategoriaIngresso::where('evento_id', $eventoId)->findOrFail($request->categoria_ingresso_id);

        if (!$categoria->temIngressosDisponiveis() || !$evento->temIngressosDisponiveis()) {
            return redirect()->back()->with('error', 'Ingressos esgotados.');
        }

        $participante = Participante::where('email', $request->participante_email)->first();
        if (!$participante) {
            $participante = Participante::create([
                'uuid' => (string) Str::uuid(),
                'nome_completo' => $request->participante_nome,
                'email' => $request->participante_email,
                'telefone' => $request->participante_telefone,
                'nuit' => $request->participante_nuit,
            ]);
        }

        for ($i = 0; $i < $request->quantidade; $i++) {
            Ingresso::create([
                'uuid' => (string) Str::uuid(),
                'qr_code' => 'EVT' . date('Y') . '.' . strtoupper(Str::random(6)) . '.' . strtoupper(Str::random(8)),
                'evento_id' => $evento->id,
                'categoria_ingresso_id' => $categoria->id,
                'participante_id' => $participante->id,
                'preco_pago' => $categoria->preco,
                'status' => 'pago',
                'data_compra' => now(),
            ]);
            $categoria->increment('quantidade_vendida');
            $evento->increment('ingressos_vendidos');
        }

        return redirect()->route('ingressos.index', $eventoId)->with('success', 'Ingressos vendidos!');
    }

    /**
     * Detalhes do ingresso.
     */
    public function show($eventoId, $id)
    {
        $ingresso = Ingresso::where('evento_id', $eventoId)->with(['evento', 'categoria', 'participante', 'checkin'])->findOrFail($id);

        // SEGURANÇA: Cliente só vê o seu ingresso
        if (auth()->user()->isCliente() && $ingresso->participante->email !== auth()->user()->email) {
            abort(403, 'Sem permissão para ver este ingresso.');
        }

        $qrCode = QrCode::size(200)->generate($ingresso->qr_code);
        return view('admin.ingressos.show', compact('ingresso', 'qrCode'));
    }

    public function cancelar($eventoId, $id)
    {
        $ingresso = Ingresso::where('evento_id', $eventoId)->findOrFail($id);

        if ($ingresso->isUtilizado() || $ingresso->isCancelado()) {
            return redirect()->back()->with('error', 'Não é possível cancelar.');
        }

        $ingresso->status = Ingresso::STATUS_CANCELADO;
        $ingresso->save();

        if ($ingresso->isPago()) {
            Evento::find($eventoId)->decrement('ingressos_vendidos');
            $ingresso->categoria->decrement('quantidade_vendida');
        }

        return redirect()->back()->with('success', 'Ingresso cancelado!');
    }

    public function gerarQRCode($eventoId, $id)
    {
        $ingresso = Ingresso::where('evento_id', $eventoId)->findOrFail($id);
        $qrCode = QrCode::size(300)->format('svg')->generate($ingresso->qr_code);
        return view('admin.ingressos.qrcode', compact('ingresso', 'qrCode'));
    }

    public function validar(Request $request, $eventoId)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
            'dispositivo_id' => 'required|exists:dispositivos,id',
            'porta_acesso' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $evento = Evento::find($eventoId);
        if (!$evento) return response()->json(['success' => false, 'message' => 'Evento não encontrado'], 404);

        $ingresso = Ingresso::where('evento_id', $eventoId)->where('qr_code', $request->qr_code)->with(['categoria', 'participante'])->first();

        if (!$ingresso) return response()->json(['success' => false, 'message' => 'Inválido', 'valid' => false], 404);

        if (!$ingresso->podeSerUtilizado()) {
            $motivo = $ingresso->isCancelado() ? 'Cancelado' : ($ingresso->isUtilizado() ? 'Já utilizado' : 'Não pago');
            return response()->json(['success' => false, 'valid' => false, 'reason' => $motivo], 422);
        }

        if ($ingresso->checkin()->exists()) return response()->json(['success' => false, 'valid' => false, 'reason' => 'Já utilizado'], 422);

        $ingresso->marcarComoUtilizado();
        $checkin = Checkin::create([
            'uuid' => (string) Str::uuid(),
            'ingresso_id' => $ingresso->id,
            'evento_id' => $eventoId,
            'dispositivo_id' => $request->dispositivo_id,
            'qr_code' => $ingresso->qr_code,
            'data_checkin' => now(),
            'porta_acesso' => $request->porta_acesso,
            'status' => 'pendente',
        ]);

        return response()->json(['success' => true, 'valid' => true, 'message' => 'Entrada liberada!', 'data' => ['ingresso' => $ingresso, 'checkin' => $checkin, 'participante' => $ingresso->participante]]);
    }
}