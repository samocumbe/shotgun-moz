<?php

/**
 * ============================================================
 * CONTROLLER: PagamentoController
 * ============================================================
 * 
 * REGRAS DE ACESSO:
 * - Cliente: Só vê pagamentos ligados aos pedidos dele.
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Pagamento;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PagamentoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pagamento::with(['pedido.participante', 'pedido.evento']);
        
        // SEGURANÇA: Cliente só vê os seus pagamentos
        if (auth()->user()->isCliente()) {
            $query->whereHas('pedido.participante', function($q) {
                $q->where('email', auth()->user()->email);
            });
        }

        if ($request->has('metodo') && $request->metodo) {
            $query->where('metodo', $request->metodo);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('pedido', function($q) use ($search) {
                $q->where('numero_pedido', 'like', "%{$search}%");
            });
        }
        
        $pagamentos = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.pagamentos.index', compact('pagamentos'));
    }

    public function show($id)
    {
        $pagamento = Pagamento::with(['pedido', 'pedido.participante'])->findOrFail($id);

        // SEGURANÇA: Cliente só vê o seu
        if (auth()->user()->isCliente() && $pagamento->pedido->participante->email !== auth()->user()->email) {
            abort(403, 'Sem permissão para ver este pagamento.');
        }

        return view('admin.pagamentos.show', compact('pagamento'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pedido_id' => 'required|exists:pedidos,id',
            'valor' => 'required|numeric|min:0.01',
            'metodo' => ['required', Rule::in(['mpesa', 'emola', 'dinheiro', 'cartao', 'transferencia'])],
            'referencia' => 'nullable|string|max:100',
            'codigo_confirmacao' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();

        $pedido = Pedido::find($request->pedido_id);
        if ($pedido->isPago()) return redirect()->back()->with('error', 'Este pedido já foi pago.');

        Pagamento::create([
            'uuid' => (string) Str::uuid(),
            'pedido_id' => $request->pedido_id,
            'valor' => $request->valor,
            'metodo' => $request->metodo,
            'referencia' => $request->referencia,
            'codigo_confirmacao' => $request->codigo_confirmacao,
            'status' => 'confirmado',
            'data_confirmacao' => now(),
        ]);

        $pedido->status = 'pago';
        $pedido->data_pagamento = now();
        $pedido->save();

        return redirect()->route('pagamentos.index')->with('success', 'Pagamento registado!');
    }

    public function confirmar($id)
    {
        $pagamento = Pagamento::findOrFail($id);

        if ($pagamento->isConfirmado()) {
            return redirect()->back()->with('error', 'Pagamento já confirmado.');
        }

        $pagamento->status = 'confirmado';
        $pagamento->data_confirmacao = now();
        $pagamento->save();

        $pedido = $pagamento->pedido;
        $pedido->status = 'pago';
        $pedido->data_pagamento = now();
        $pedido->save();

        return redirect()->route('pagamentos.index')->with('success', 'Pagamento confirmado!');
    }
}