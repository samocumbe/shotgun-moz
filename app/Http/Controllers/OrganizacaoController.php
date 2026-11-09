<?php

namespace App\Http\Controllers;

use App\Models\Organizacao;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizacaoController extends Controller
{
    /**
     * Lista todas as organizações
     */
    public function index()
    {
        $organizacoes = Organizacao::withCount(['usuarios', 'eventos'])
                                   ->orderBy('nome')
                                   ->paginate(15);
        return view('admin.organizacoes.index', compact('organizacoes'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return view('admin.organizacoes.create');
    }

    /**
     * Salvar nova organização
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255|unique:organizacoes,nome',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'descricao' => 'nullable|string',
            'status' => ['required', Rule::in(['ativo', 'inativo', 'suspenso'])],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $organizacao = Organizacao::create([
            'uuid' => (string) Str::uuid(),
            'nome' => $request->nome,
            'slug' => Str::slug($request->nome . '-' . uniqid()),
            'email' => $request->email,
            'telefone' => $request->telefone,
            'endereco' => $request->endereco,
            'website' => $request->website,
            'descricao' => $request->descricao,
            'status' => $request->status,
        ]);

        return redirect()->route('organizacoes.index')
            ->with('success', '✅ Organização criada com sucesso!');
    }

    /**
     * Mostrar detalhes da organização
     */
    public function show($id)
    {
        $organizacao = Organizacao::with(['usuarios', 'eventos'])
                                  ->findOrFail($id);
        return view('admin.organizacoes.show', compact('organizacao'));
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $organizacao = Organizacao::findOrFail($id);
        return view('admin.organizacoes.edit', compact('organizacao'));
    }

    /**
     * Atualizar organização
     */
    public function update(Request $request, $id)
    {
        $organizacao = Organizacao::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome' => ['required', 'string', 'max:255', Rule::unique('organizacoes')->ignore($organizacao->id)],
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'descricao' => 'nullable|string',
            'status' => ['required', Rule::in(['ativo', 'inativo', 'suspenso'])],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $organizacao->update($request->all());

        return redirect()->route('organizacoes.index')
            ->with('success', '✅ Organização atualizada com sucesso!');
    }

    /**
     * Excluir organização
     */
    public function destroy($id)
    {
        $organizacao = Organizacao::findOrFail($id);

        if ($organizacao->eventos()->count() > 0) {
            return redirect()->back()
                ->with('error', '❌ Não é possível excluir uma organização com eventos associados.');
        }

        $organizacao->delete();

        return redirect()->route('organizacoes.index')
            ->with('success', '✅ Organização removida com sucesso!');
    }
}