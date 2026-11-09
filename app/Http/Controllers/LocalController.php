<?php

/**
 * Controlador de Locais
 * 
 * Gerencia todas as operações relacionadas a locais de eventos
 * Inclui: listagem, criação, edição, visualização e exclusão
 */

namespace App\Http\Controllers;

use App\Models\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LocalController extends Controller
{
    /**
     * Lista todos os locais com paginação
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Busca todos os locais com paginação de 15 itens por página
        $locais = Local::paginate(15);
        
        // Retorna a view com os dados
        return view('admin.locais.index', compact('locais'));
    }

    /**
     * Mostra o formulário para criar um novo local
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Retorna a view de criação
        return view('admin.locais.create');
    }

    /**
     * Salva um novo local no banco de dados
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validação dos dados recebidos
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255|unique:locais,nome',
            'endereco' => 'required|string|max:500',
            'cidade' => 'required|string|max:100',
            'provincia' => 'required|string|max:100',
            'capacidade' => 'required|integer|min:1',
            'descricao' => 'nullable|string|max:1000',
        ]);

        // Se houver erros de validação, retorna com os erros
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Cria o novo local no banco de dados
        Local::create($request->all());

        // Redireciona para a lista com mensagem de sucesso
        return redirect()->route('locais.index')
            ->with('success', 'Local criado com sucesso!');
    }

    /**
     * Exibe os detalhes de um local específico
     * 
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Busca o local com seus eventos relacionados
        $local = Local::with('eventos')->findOrFail($id);
        
        // Retorna a view de detalhes com os dados do local
        return view('admin.locais.show', compact('local'));
    }

    /**
     * Mostra o formulário para editar um local
     * 
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Busca o local pelo ID ou retorna erro 404
        $local = Local::findOrFail($id);
        
        // Retorna a view de edição com os dados do local
        return view('admin.locais.edit', compact('local'));
    }

    /**
     * Atualiza um local existente
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Busca o local pelo ID
        $local = Local::findOrFail($id);

        // Validação dos dados recebidos
        // A regra 'unique' ignora o próprio ID para não gerar conflito
        $validator = Validator::make($request->all(), [
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('locais', 'nome')->ignore($id)
            ],
            'endereco' => 'required|string|max:500',
            'cidade' => 'required|string|max:100',
            'provincia' => 'required|string|max:100',
            'capacidade' => 'required|integer|min:1',
            'descricao' => 'nullable|string|max:1000',
        ]);

        // Se houver erros de validação, retorna com os erros
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Atualiza os dados do local
        $local->update($request->all());

        // Redireciona para a lista com mensagem de sucesso
        return redirect()->route('locais.index')
            ->with('success', 'Local atualizado com sucesso!');
    }

    /**
     * Remove um local do banco de dados
     * 
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Busca o local pelo ID
        $local = Local::findOrFail($id);
        
        // Verifica se o local possui eventos associados
        if ($local->eventos()->count() > 0) {
            // Se tiver eventos, não permite exclusão
            return redirect()->route('locais.index')
                ->with('error', 'Não é possível excluir um local que possui eventos!');
        }

        // Remove o local do banco de dados
        $local->delete();

        // Redireciona para a lista com mensagem de sucesso
        return redirect()->route('locais.index')
            ->with('success', 'Local removido com sucesso!');
    }

    /**
     * Lista todos os eventos de um local específico
     * 
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function eventos($id)
    {
        // Busca o local pelo ID
        $local = Local::findOrFail($id);
        
        // Busca todos os eventos do local com paginação
        $eventos = $local->eventos()->paginate(15);
        
        // Retorna a view com os dados
        return view('admin.locais.eventos', compact('local', 'eventos'));
    }
}