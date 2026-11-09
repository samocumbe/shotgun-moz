<?php

namespace App\Http\Controllers;

use App\Models\Promoter;
use App\Models\Evento;
use App\Models\Comissao;
use App\Models\VendaPromoter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PromoterController extends Controller
{
    public function index(Request $request)
    {
        $query = Promoter::query();
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('codigo_promocional', 'like', "%{$search}%");
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $promoters = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.promoters.index', compact('promoters'));
    }

    public function create()
    {
        $eventos = Evento::where('status', 'publicado')->get();
        return view('admin.promoters.create', compact('eventos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:promoters,email',
            'telefone' => 'required|string|max:20',
            'nuit' => 'nullable|string|max:20',
            'comissao_padrao' => 'required|numeric|min:0|max:100',
            'eventos' => 'nullable|array',
            'eventos.*' => 'exists:eventos,id',
            'status' => ['required', Rule::in(['ativo', 'inativo', 'pendente'])],
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $promoter = Promoter::create([
            'uuid' => (string) Str::uuid(),
            'nome' => $request->nome,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'nuit' => $request->nuit,
            'comissao_padrao' => $request->comissao_padrao,
            'status' => $request->status,
            'observacoes' => $request->observacoes,
        ]);

        if ($request->has('eventos')) {
            $promoter->eventos()->attach($request->eventos);
        }

        return redirect()->route('promoters.index')
            ->with('success', 'Promoter criado com sucesso!');
    }

    public function show($id)
    {
        $promoter = Promoter::with(['eventos', 'comissoes', 'vendasPromoter'])
                            ->findOrFail($id);
        
        $estatisticas = [
            'total_vendas' => $promoter->vendasPromoter()->count(),
            'total_ingressos' => $promoter->vendasPromoter()->sum('valor_total'),
            'total_comissao' => $promoter->comissoes()->sum('valor_comissao'),
            'vendas_por_evento' => $promoter->vendasPromoter()
                ->with('venda.evento')
                ->get()
                ->groupBy('venda.evento_id')
                ->map(function($vendas) {
                    return [
                        'evento' => $vendas->first()->venda->evento->nome ?? 'N/A',
                        'total' => $vendas->count(),
                        'valor' => $vendas->sum('valor_total'),
                        'comissao' => $vendas->sum('comissao_gerada'),
                    ];
                }),
        ];
        
        return view('admin.promoters.show', compact('promoter', 'estatisticas'));
    }

    public function edit($id)
    {
        $promoter = Promoter::findOrFail($id);
        $eventos = Evento::where('status', 'publicado')->get();
        $eventosSelecionados = $promoter->eventos()->pluck('evento_id')->toArray();
        return view('admin.promoters.edit', compact('promoter', 'eventos', 'eventosSelecionados'));
    }

    public function update(Request $request, $id)
    {
        $promoter = Promoter::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:promoters,email,' . $id,
            'telefone' => 'required|string|max:20',
            'nuit' => 'nullable|string|max:20',
            'comissao_padrao' => 'required|numeric|min:0|max:100',
            'eventos' => 'nullable|array',
            'eventos.*' => 'exists:eventos,id',
            'status' => ['required', Rule::in(['ativo', 'inativo', 'pendente'])],
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $promoter->update([
            'nome' => $request->nome,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'nuit' => $request->nuit,
            'comissao_padrao' => $request->comissao_padrao,
            'status' => $request->status,
            'observacoes' => $request->observacoes,
        ]);

        $promoter->eventos()->sync($request->eventos ?? []);

        return redirect()->route('promoters.index')
            ->with('success', 'Promoter atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $promoter = Promoter::findOrFail($id);
        $promoter->delete();

        return redirect()->route('promoters.index')
            ->with('success', 'Promoter removido com sucesso!');
    }

    public function comissoes($id)
    {
        $promoter = Promoter::findOrFail($id);
        $comissoes = $promoter->comissoes()
                              ->with(['venda', 'pedido'])
                              ->orderBy('created_at', 'desc')
                              ->paginate(15);
        
        return view('admin.promoters.comissoes', compact('promoter', 'comissoes'));
    }

    public function pagarComissao($id)
    {
        $comissao = Comissao::findOrFail($id);
        $comissao->status = 'pago';
        $comissao->data_pagamento = now();
        $comissao->save();

        return redirect()->back()
            ->with('success', 'Comissão marcada como paga!');
    }
}