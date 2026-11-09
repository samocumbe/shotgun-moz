<?php

/**
 * ============================================================
 * CONTROLLER: PedidoController
 * ============================================================
 * 
 * REGRAS DE ACESSO:
 * - Cliente: Só vê pedidos do email dele.
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Evento;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['evento', 'participante', 'user', 'itens.categoria']);
        
        // SEGURANÇA: Cliente só vê os seus pedidos
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
        
        if ($request->has('tipo_venda') && $request->tipo_venda) {
            $query->where('tipo_venda', $request->tipo_venda);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_pedido', 'like', "%{$search}%")
                  ->orWhereHas('participante', function($p) use ($search) {
                      $p->where('nome_completo', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $pedidos = $query->orderBy('created_at', 'desc')->paginate(15);
        $eventos = Evento::where('status', 'publicado')->get();
        
        return view('admin.pedidos.index', compact('pedidos', 'eventos'));
    }

    public function show($id)
    {
        $pedido = Pedido::with(['evento', 'participante', 'user', 'itens.categoria', 'itens.ingresso', 'pagamentos'])->findOrFail($id);
        
        // SEGURANÇA: Cliente só vê o seu pedido
        if (auth()->user()->isCliente() && $pedido->participante->email !== auth()->user()->email) {
            abort(403, 'Sem permissão para ver este pedido.');
        }

        return view('admin.pedidos.show', compact('pedido'));
    }

    public function updateStatus(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->status = $request->status;
        $pedido->save();
        
        return redirect()->route('pedidos.show', $pedido->id)->with('success', 'Status atualizado!');
    }

    public function porParticipante($participanteId)
    {
        // Segurança: Cliente só pode ver os seus
        if (auth()->user()->isCliente()) {
            $participante = \App\Models\Participante::find($participanteId);
            if (!$participante || $participante->email !== auth()->user()->email) {
                return response()->json(['success' => false, 'message' => 'Sem permissão'], 403);
            }
        }

        $pedidos = Pedido::where('participante_id', $participanteId)->with(['evento', 'itens.categoria'])->orderBy('created_at', 'desc')->get();

        return response()->json(['success' => true, 'data' => $pedidos, 'message' => 'Pedidos listados']);
    }
}