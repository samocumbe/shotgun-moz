<?php

/**
 * ============================================================
 * CONTROLLER: CupomController (Descontos)
 * ============================================================
 * 
 * Gerencia todas as operações relacionadas a descontos:
 * - Listagem com filtros
 * - Criação de novos descontos
 * - Edição de descontos existentes
 * - Validação de descontos (via API)
 * - Aplicação de descontos
 * 
 * Em Moçambique, o termo usado é "Desconto" em vez de "Cupom".
 * As rotas estão configuradas como /admin/descontos
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Cupom;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CupomController extends Controller
{
    /**
     * ============================================================
     * INDEX: Lista todos os descontos com filtros
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Cupom::with(['evento']);
        
        // Filtrar por evento
        if ($request->has('evento_id') && $request->evento_id) {
            $query->where('evento_id', $request->evento_id);
        }
        
        // Filtrar por status (ativo/inativo)
        if ($request->has('ativo') && $request->ativo !== '') {
            $query->where('ativo', $request->ativo);
        }
        
        // Filtrar por tipo
        if ($request->has('tipo') && $request->tipo) {
            $query->where('tipo', $request->tipo);
        }
        
        // Busca por texto
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('codigo', 'like', "%{$search}%")
                  ->orWhere('nome', 'like', "%{$search}%");
        }
        
        // Paginar os resultados (15 por página)
        $cupons = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Buscar eventos para o filtro
        $eventos = Evento::where('status', 'publicado')->get();
        
        // A view usa o termo "descontos" mas a variável mantém "cupons"
        return view('admin.descontos.index', compact('cupons', 'eventos'));
    }

    /**
     * ============================================================
     * CREATE: Mostra o formulário para criar um novo desconto
     * ============================================================
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $eventos = Evento::where('status', 'publicado')->get();
        return view('admin.descontos.create', compact('eventos'));
    }

    /**
     * ============================================================
     * STORE: Salva um novo desconto no banco de dados
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:50|unique:cupons,codigo',
            'nome' => 'required|string|max:255',
            'evento_id' => 'nullable|exists:eventos,id',
            'tipo' => ['required', Rule::in(['percentual', 'fixo'])],
            'valor' => 'required|numeric|min:0.01',
            'valor_minimo' => 'nullable|numeric|min:0',
            'quantidade' => 'nullable|integer|min:1',
            'validade_inicio' => 'nullable|date',
            'validade_fim' => 'nullable|date|after_or_equal:validade_inicio',
            'ativo' => 'nullable|boolean',
        ]);

        // Se houver erros, retorna com mensagens
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Criar o desconto
        $cupom = Cupom::create([
            'codigo' => strtoupper($request->codigo),
            'nome' => $request->nome,
            'evento_id' => $request->evento_id,
            'tipo' => $request->tipo,
            'valor' => $request->valor,
            'valor_minimo' => $request->valor_minimo ?? 0,
            'quantidade' => $request->quantidade,
            'usos' => 0,
            'validade_inicio' => $request->validade_inicio,
            'validade_fim' => $request->validade_fim,
            'ativo' => $request->has('ativo'),
        ]);

        // Redireciona com mensagem de sucesso
        return redirect()->route('descontos.index')
            ->with('success', 'Desconto criado com sucesso!');
    }

    /**
     * ============================================================
     * SHOW: Exibe os detalhes de um desconto específico
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $cupom = Cupom::with('evento')->findOrFail($id);
        return view('admin.descontos.show', compact('cupom'));
    }

    /**
     * ============================================================
     * EDIT: Mostra o formulário para editar um desconto
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $cupom = Cupom::findOrFail($id);
        $eventos = Evento::where('status', 'publicado')->get();
        return view('admin.descontos.edit', compact('cupom', 'eventos'));
    }

    /**
     * ============================================================
     * UPDATE: Atualiza um desconto existente
     * ============================================================
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $cupom = Cupom::findOrFail($id);

        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cupons', 'codigo')->ignore($id)
            ],
            'nome' => 'required|string|max:255',
            'evento_id' => 'nullable|exists:eventos,id',
            'tipo' => ['required', Rule::in(['percentual', 'fixo'])],
            'valor' => 'required|numeric|min:0.01',
            'valor_minimo' => 'nullable|numeric|min:0',
            'quantidade' => 'nullable|integer|min:1',
            'validade_inicio' => 'nullable|date',
            'validade_fim' => 'nullable|date|after_or_equal:validade_inicio',
            'ativo' => 'nullable|boolean',
        ]);

        // Se houver erros, retorna com mensagens
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Atualizar o desconto
        $cupom->update([
            'codigo' => strtoupper($request->codigo),
            'nome' => $request->nome,
            'evento_id' => $request->evento_id,
            'tipo' => $request->tipo,
            'valor' => $request->valor,
            'valor_minimo' => $request->valor_minimo ?? 0,
            'quantidade' => $request->quantidade,
            'validade_inicio' => $request->validade_inicio,
            'validade_fim' => $request->validade_fim,
            'ativo' => $request->has('ativo'),
        ]);

        // Redireciona com mensagem de sucesso
        return redirect()->route('descontos.index')
            ->with('success', 'Desconto atualizado com sucesso!');
    }

    /**
     * ============================================================
     * DESTROY: Remove um desconto do banco de dados
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $cupom = Cupom::findOrFail($id);
        $cupom->delete();

        return redirect()->route('descontos.index')
            ->with('success', 'Desconto removido com sucesso!');
    }

    /**
     * ============================================================
     * VALIDAR: Valida um desconto (API/JSON)
     * ============================================================
     * 
     * Usado para verificar se um código de desconto é válido
     * antes de aplicar à compra.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|exists:cupons,codigo',
            'subtotal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Erro de validação'
            ], 422);
        }

        $cupom = Cupom::where('codigo', strtoupper($request->codigo))->first();

        if (!$cupom->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Desconto inválido ou expirado'
            ], 422);
        }

        $desconto = $cupom->calcularDesconto($request->subtotal);

        return response()->json([
            'success' => true,
            'data' => [
                'cupom' => $cupom,
                'desconto' => $desconto,
                'subtotal' => $request->subtotal,
                'total' => $request->subtotal - $desconto,
            ],
            'message' => 'Desconto válido'
        ]);
    }

    /**
     * ============================================================
     * APLICAR: Aplica um desconto (incrementa o contador de usos)
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function aplicar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|exists:cupons,codigo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Erro de validação'
            ], 422);
        }

        $cupom = Cupom::where('codigo', strtoupper($request->codigo))->first();

        if (!$cupom->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Desconto inválido ou expirado'
            ], 422);
        }

        $cupom->increment('usos');

        return response()->json([
            'success' => true,
            'data' => $cupom,
            'message' => 'Desconto aplicado com sucesso'
        ]);
    }
}