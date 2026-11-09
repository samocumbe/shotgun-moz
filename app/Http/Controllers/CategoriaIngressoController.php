<?php

/**
 * ============================================================
 * CONTROLLER: CategoriaIngressoController
 * ============================================================
 * 
 * Gerencia todas as operações CRUD para categorias de ingressos:
 * - Listagem de categorias de um evento
 * - Criação de nova categoria
 * - Visualização de categoria específica
 * - Edição de categoria existente
 * - Exclusão de categoria
 * 
 * Todas as operações são vinculadas a um evento específico.
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\CategoriaIngresso;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoriaIngressoController extends Controller
{
    /**
     * ============================================================
     * INDEX: Lista todas as categorias de um evento
     * ============================================================
     * 
     * @param int $eventoId
     * @return \Illuminate\View\View
     */
    public function index($eventoId)
    {
        $evento = Evento::findOrFail($eventoId);
        $categorias = $evento->categoriasIngressos()->get();
        
        return view('admin.categorias.index', compact('evento', 'categorias'));
    }

    /**
     * ============================================================
     * CREATE: Mostra o formulário para criar nova categoria
     * ============================================================
     * 
     * @param int $eventoId
     * @return \Illuminate\View\View
     */
    public function create($eventoId)
    {
        $evento = Evento::findOrFail($eventoId);
        
        // Verificar se o evento está publicado
        if ($evento->isPublicado()) {
            return redirect()->route('eventos.show', $eventoId)
                ->with('error', 'Não é possível adicionar categorias a um evento já publicado');
        }
        
        return view('admin.categorias.create', compact('evento'));
    }

    /**
     * ============================================================
     * STORE: Salva uma nova categoria no banco de dados
     * ============================================================
     * 
     * @param Request $request
     * @param int $eventoId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $eventoId)
    {
        $evento = Evento::findOrFail($eventoId);

        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
            'preco' => 'required|numeric|min:0',
            'quantidade_total' => 'required|integer|min:1',
            'tipo' => ['required', Rule::in([
                CategoriaIngresso::TIPO_NORMAL,
                CategoriaIngresso::TIPO_VIP,
                CategoriaIngresso::TIPO_BACKSTAGE,
                CategoriaIngresso::TIPO_MEIA,
                CategoriaIngresso::TIPO_ESTUDANTE
            ])],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar se a quantidade total não excede a capacidade do evento
        $totalAtual = $evento->categoriasIngressos()->sum('quantidade_total');
        $novaQuantidade = $request->quantidade_total;
        
        if ($totalAtual + $novaQuantidade > $evento->capacidade_total) {
            return redirect()->back()
                ->with('error', 'A quantidade total de ingressos excede a capacidade do evento')
                ->withInput();
        }

        // Criar a categoria
        $categoria = CategoriaIngresso::create([
            'evento_id' => $eventoId,
            'nome' => $request->nome,
            'slug' => Str::slug($request->nome . '-' . uniqid()),
            'descricao' => $request->descricao,
            'preco' => $request->preco,
            'quantidade_total' => $request->quantidade_total,
            'quantidade_vendida' => 0,
            'tipo' => $request->tipo,
        ]);

        return redirect()->route('eventos.show', $eventoId)
            ->with('success', 'Categoria criada com sucesso!');
    }

    /**
     * ============================================================
     * SHOW: Exibe os detalhes de uma categoria específica
     * ============================================================
     * 
     * @param int $eventoId
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($eventoId, $id)
    {
        $categoria = CategoriaIngresso::where('evento_id', $eventoId)
                                       ->with(['evento', 'ingressos'])
                                       ->findOrFail($id);
        
        $evento = Evento::findOrFail($eventoId);
        
        return view('admin.categorias.show', compact('categoria', 'evento'));
    }

    /**
     * ============================================================
     * EDIT: Mostra o formulário para editar uma categoria
     * ============================================================
     * 
     * @param int $eventoId
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($eventoId, $id)
    {
        $categoria = CategoriaIngresso::where('evento_id', $eventoId)
                                       ->findOrFail($id);
        $evento = Evento::findOrFail($eventoId);
        
        // Verificar se o evento está publicado
        if ($evento->isPublicado()) {
            return redirect()->route('eventos.show', $eventoId)
                ->with('error', 'Não é possível alterar categorias de um evento já publicado');
        }
        
        return view('admin.categorias.edit', compact('categoria', 'evento'));
    }

    /**
     * ============================================================
     * UPDATE: Atualiza uma categoria existente
     * ============================================================
     * 
     * @param Request $request
     * @param int $eventoId
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $eventoId, $id)
    {
        $categoria = CategoriaIngresso::where('evento_id', $eventoId)
                                       ->findOrFail($id);
        $evento = Evento::findOrFail($eventoId);

        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
            'preco' => 'required|numeric|min:0',
            'quantidade_total' => 'required|integer|min:1',
            'tipo' => ['required', Rule::in([
                CategoriaIngresso::TIPO_NORMAL,
                CategoriaIngresso::TIPO_VIP,
                CategoriaIngresso::TIPO_BACKSTAGE,
                CategoriaIngresso::TIPO_MEIA,
                CategoriaIngresso::TIPO_ESTUDANTE
            ])],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar a capacidade total se quantidade_total for alterada
        if ($request->has('quantidade_total')) {
            $totalAtual = $evento->categoriasIngressos()
                                 ->where('id', '!=', $id)
                                 ->sum('quantidade_total');
            
            if ($totalAtual + $request->quantidade_total > $evento->capacidade_total) {
                return redirect()->back()
                    ->with('error', 'A quantidade total de ingressos excede a capacidade do evento')
                    ->withInput();
            }
        }

        // Atualizar a categoria
        $categoria->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'preco' => $request->preco,
            'quantidade_total' => $request->quantidade_total,
            'tipo' => $request->tipo,
        ]);

        return redirect()->route('eventos.show', $eventoId)
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * ============================================================
     * DESTROY: Remove uma categoria do banco de dados
     * ============================================================
     * 
     * @param int $eventoId
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($eventoId, $id)
    {
        $categoria = CategoriaIngresso::where('evento_id', $eventoId)
                                       ->findOrFail($id);
        $evento = Evento::findOrFail($eventoId);

        // Verificar se tem ingressos vendidos
        if ($categoria->ingressos()->whereIn('status', ['pago', 'utilizado'])->exists()) {
            return redirect()->route('eventos.show', $eventoId)
                ->with('error', 'Não é possível excluir uma categoria que possui ingressos vendidos');
        }

        // Verificar se tem ingressos disponíveis (não vendidos)
        if ($categoria->ingressos()->where('status', 'disponivel')->exists()) {
            return redirect()->route('eventos.show', $eventoId)
                ->with('error', 'Não é possível excluir uma categoria que possui ingressos disponíveis');
        }

        $categoria->delete();

        return redirect()->route('eventos.show', $eventoId)
            ->with('success', 'Categoria removida com sucesso!');
    }
}