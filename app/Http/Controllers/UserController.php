<?php

/**
 * ============================================================
 * CONTROLLER: UserController
 * ============================================================
 * 
 * Gerencia todas as operações relacionadas a usuários:
 * - Listagem de usuários (com filtro por organização)
 * - Criação de novos usuários
 * - Visualização de detalhes
 * - Edição de usuários (com vínculo à organização)
 * - Exclusão de usuários
 * 
 * Multi-Organizador:
 * - Filtro de usuários por organização
 * - Vínculo de usuários a organizações
 * - Marcação de organizadores
 * - Admin vê todos os usuários
 * - Organizador vê apenas usuários da sua organização
 * 
 * Suporta perfis: admin, operador, promoter, cliente
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organizacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    /**
     * ============================================================
     * INDEX: Lista todos os usuários com filtros
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     * 
     * Filtros disponíveis:
     * - search: Busca por nome ou email
     * - role: admin, operador, promoter, cliente
     * 
     * 🔥 Multi-Organizador: Usuários não-admin só veem usuários da sua organização
     */
    public function index(Request $request)
    {
        $query = User::query();

        // ============================================================
        // 🔥 MULTI-ORGANIZADOR: Filtrar por organização
        // ============================================================
        if (!auth()->user()->isAdmin()) {
            $query->where('organizacao_id', auth()->user()->organizacao_id);
        }

        // Filtro por busca
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        // Filtro por papel/role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // 🔥 MULTI-ORGANIZADOR: Filtro por organização (apenas admin)
        if ($request->has('organizacao_id') && $request->organizacao_id && auth()->user()->isAdmin()) {
            $query->where('organizacao_id', $request->organizacao_id);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Estatísticas
        $total = User::count();
        $admins = User::where('role', 'admin')->count();
        $operadores = User::where('role', 'operador')->count();
        $promoters = User::where('role', 'promoter')->count();
        $clientes = User::where('role', 'cliente')->count();

        // 🔥 MULTI-ORGANIZADOR: Buscar organizações para o filtro (admin)
        $organizacoes = [];
        if (auth()->user()->isAdmin()) {
            $organizacoes = Organizacao::where('status', 'ativo')->orderBy('nome')->get();
        }

        return view('admin.users.index', compact('users', 'total', 'admins', 'operadores', 'promoters', 'clientes', 'organizacoes'));
    }

    /**
     * ============================================================
     * CREATE: Mostra o formulário para criar novo usuário
     * ============================================================
     * 
     * @return \Illuminate\View\View
     * 
     * 🔥 Multi-Organizador: Carrega organizações para seleção
     */
    public function create()
    {
        $roles = User::ROLES;
        
        // 🔥 MULTI-ORGANIZADOR: Buscar organizações
        $organizacoes = Organizacao::where('status', 'ativo')->orderBy('nome')->get();
        
        return view('admin.users.create', compact('roles', 'organizacoes'));
    }

    /**
     * ============================================================
     * STORE: Salva um novo usuário no banco de dados
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * 
     * 🔥 Multi-Organizador: Permite vincular usuário a uma organização
     */
    public function store(Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefone' => 'nullable|string|max:20',
            'nuit' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'organizacao_id' => 'nullable|exists:organizacoes,id', // 🔥 MULTI-ORGANIZADOR
            'is_organizador' => 'nullable|boolean', // 🔥 MULTI-ORGANIZADOR
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Dados do usuário
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'nuit' => $request->nuit,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_admin' => $request->role === User::ROLE_ADMIN,
            'organizacao_id' => $request->organizacao_id, // 🔥 MULTI-ORGANIZADOR
            'is_organizador' => $request->has('is_organizador') ? true : false, // 🔥 MULTI-ORGANIZADOR
        ];

        $user = User::create($userData);

        return redirect()->route('users.index')
            ->with('success', "Usuário {$user->name} criado com sucesso!");
    }

    /**
     * ============================================================
     * SHOW: Exibe os detalhes de um usuário específico
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\View\View
     * 
     * 🔥 Multi-Organizador: Verifica permissão antes de exibir
     */
    public function show($id)
    {
        $user = User::with(['carteira.ingresso.evento', 'dispositivos', 'organizacao'])
                    ->findOrFail($id);

        // ============================================================
        // 🔥 MULTI-ORGANIZADOR: Verificar permissão
        // ============================================================
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id !== $user->organizacao_id) {
            abort(403, 'Você não tem permissão para visualizar este usuário.');
        }

        $totalIngressos = $user->carteira->count();
        $ativos = $user->carteira->where('status', 'ativo')->count();
        $utilizados = $user->carteira->where('status', 'utilizado')->count();
        $transferidos = $user->carteira->where('status', 'transferido')->count();

        return view('admin.users.show', compact(
            'user',
            'totalIngressos',
            'ativos',
            'utilizados',
            'transferidos'
        ));
    }

    /**
     * ============================================================
     * EDIT: Mostra o formulário para editar um usuário
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\View\View
     * 
     * 🔥 Multi-Organizador: Carrega organizações para seleção
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = User::ROLES;
        
        // ============================================================
        // 🔥 MULTI-ORGANIZADOR: Verificar permissão
        // ============================================================
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id !== $user->organizacao_id) {
            abort(403, 'Você não tem permissão para editar este usuário.');
        }
        
        // 🔥 MULTI-ORGANIZADOR: Buscar organizações
        $organizacoes = Organizacao::where('status', 'ativo')->orderBy('nome')->get();
        
        return view('admin.users.edit', compact('user', 'roles', 'organizacoes'));
    }

    /**
     * ============================================================
     * UPDATE: Atualiza os dados de um usuário
     * ============================================================
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * 
     * 🔥 Multi-Organizador: Atualiza vínculo com organização
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // ============================================================
        // 🔥 MULTI-ORGANIZADOR: Verificar permissão
        // ============================================================
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id !== $user->organizacao_id) {
            abort(403, 'Você não tem permissão para editar este usuário.');
        }

        // Validação
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'telefone' => 'nullable|string|max:20',
            'nuit' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'organizacao_id' => 'nullable|exists:organizacoes,id', // 🔥 MULTI-ORGANIZADOR
            'is_organizador' => 'nullable|boolean', // 🔥 MULTI-ORGANIZADOR
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Dados para atualizar
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'nuit' => $request->nuit,
            'role' => $request->role,
            'is_admin' => $request->role === User::ROLE_ADMIN,
            'organizacao_id' => $request->organizacao_id, // 🔥 MULTI-ORGANIZADOR
            'is_organizador' => $request->has('is_organizador') ? true : false, // 🔥 MULTI-ORGANIZADOR
        ];

        // Atualizar senha apenas se for fornecida
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', "Usuário {$user->name} atualizado com sucesso!");
    }

    /**
     * ============================================================
     * DESTROY: Remove um usuário do sistema
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * 
     * 🔥 Multi-Organizador: Verifica permissão antes de excluir
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // ============================================================
        // 🔥 MULTI-ORGANIZADOR: Verificar permissão
        // ============================================================
        if (!auth()->user()->isAdmin() && auth()->user()->organizacao_id !== $user->organizacao_id) {
            abort(403, 'Você não tem permissão para excluir este usuário.');
        }

        // Não permitir excluir o próprio usuário
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Não é possível excluir seu próprio usuário.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Usuário {$userName} excluído com sucesso!");
    }
}