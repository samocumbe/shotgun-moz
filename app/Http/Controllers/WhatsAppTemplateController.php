<?php

/**
 * ============================================================
 * CONTROLLER: WhatsAppTemplateController - SHOTGUN-MOZ
 * ============================================================
 *
 * Gerencia o CRUD de templates de mensagens WhatsApp.
 *
 * Templates são modelos reutilizáveis de mensagens que podem
 * conter variáveis no formato {{variavel}} que são substituídas
 * no momento do envio.
 *
 * Categorias de template (alinhadas com WhatsApp Business API):
 * - marketing    → Promoções, novidades, ofertas
 * - utilidade    → Confirmações, lembretes, atualizações de estado
 * - autenticacao → Códigos de verificação, OTP
 *
 * Status do template:
 * - pendente  → Aguardando aprovação
 * - aprovado  → Pronto a usar
 * - rejeitado → Não aprovado (ex: conteúdo inadequado)
 *
 * Multi-Organizador:
 * - Organizadores só vêem/gere seus templates
 * - Admin vê/gere todos
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\WhatsAppTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WhatsAppTemplateController extends Controller
{
    /**
     * ============================================================
     * INDEX: Lista todos os templates WhatsApp
     * ============================================================
     */
    public function index(Request $request)
    {
        $query = WhatsAppTemplate::query();

        // Filtro por organização
        if (!Auth::user()->isAdmin() && Auth::user()->organizacao_id) {
            $query->where('organizacao_id', Auth::user()->organizacao_id);
        }

        // Filtro por busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        // Filtro por categoria
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por ativo/inativo
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $templates = $query->orderBy('nome')->paginate(20);

        // Estatísticas
        $statsQuery = WhatsAppTemplate::query();
        if (!Auth::user()->isAdmin() && Auth::user()->organizacao_id) {
            $statsQuery->where('organizacao_id', Auth::user()->organizacao_id);
        }

        $estatisticas = [
            'total'     => (clone $statsQuery)->count(),
            'ativos'    => (clone $statsQuery)->where('is_active', true)->count(),
            'aprovados' => (clone $statsQuery)->where('status', 'aprovado')->count(),
            'pendentes' => (clone $statsQuery)->where('status', 'pendente')->count(),
        ];

        return view('admin.whatsapp.templates.index', compact('templates', 'estatisticas'));
    }

    /**
     * ============================================================
     * CREATE: Formulário de criação
     * ============================================================
     */
    public function create()
    {
        return view('admin.whatsapp.templates.create');
    }

    /**
     * ============================================================
     * STORE: Guarda novo template
     * ============================================================
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'nome'      => 'required|string|max:100',
            'codigo'    => 'required|string|max:100|unique:whatsapp_templates,codigo|regex:/^[A-Z0-9_]+$/',
            'mensagem'  => 'required|string|max:4096',
            'categoria' => ['required', Rule::in(['marketing', 'utilidade', 'autenticacao'])],
            'is_active' => 'nullable|boolean',
        ], [
            'codigo.regex'  => 'O código só pode conter letras maiúsculas, números e underscores (_).',
            'codigo.unique' => 'Já existe um template com este código.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Extrair variáveis da mensagem (formato {{variavel}} ou {variavel})
        $variaveis = $this->extrairVariaveis($request->mensagem);

        $template = WhatsAppTemplate::create([
            'uuid'           => (string) Str::uuid(),
            'organizacao_id' => Auth::user()->organizacao_id,
            'nome'           => $request->nome,
            'codigo'         => strtoupper($request->codigo),
            'mensagem'       => $request->mensagem,
            'variaveis'      => $variaveis,
            'categoria'      => $request->categoria,
            'status'         => 'pendente',
            'is_active'      => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('whatsapp.templates.index')
            ->with('success', "✅ Template \"{$template->nome}\" criado com sucesso!");
    }

    /**
     * ============================================================
     * SHOW: Detalhes do template
     * ============================================================
     */
    public function show($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);

        $this->autorizarAcesso($template);

        return view('admin.whatsapp.templates.show', compact('template'));
    }

    /**
     * ============================================================
     * EDIT: Formulário de edição
     * ============================================================
     */
    public function edit($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);

        $this->autorizarAcesso($template);

        return view('admin.whatsapp.templates.edit', compact('template'));
    }

    /**
     * ============================================================
     * UPDATE: Atualizar template
     * ============================================================
     */
    public function update(Request $request, $id)
    {
        $template = WhatsAppTemplate::findOrFail($id);

        $this->autorizarAcesso($template);

        $validator = validator($request->all(), [
            'nome'      => 'required|string|max:100',
            'codigo'    => [
                'required', 'string', 'max:100',
                Rule::unique('whatsapp_templates', 'codigo')->ignore($template->id),
                'regex:/^[A-Z0-9_]+$/',
            ],
            'mensagem'  => 'required|string|max:4096',
            'categoria' => ['required', Rule::in(['marketing', 'utilidade', 'autenticacao'])],
            'is_active' => 'nullable|boolean',
        ], [
            'codigo.regex'  => 'O código só pode conter letras maiúsculas, números e underscores (_).',
            'codigo.unique' => 'Já existe outro template com este código.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Re-extrair variáveis
        $variaveis = $this->extrairVariaveis($request->mensagem);

        $template->update([
            'nome'      => $request->nome,
            'codigo'    => strtoupper($request->codigo),
            'mensagem'  => $request->mensagem,
            'variaveis' => $variaveis,
            'categoria' => $request->categoria,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('whatsapp.templates.index')
            ->with('success', "✅ Template \"{$template->nome}\" atualizado com sucesso!");
    }

    /**
     * ============================================================
     * DESTROY: Apagar template
     * ============================================================
     */
    public function destroy($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);

        $this->autorizarAcesso($template);

        $nome = $template->nome;
        $template->delete();

        return redirect()->route('whatsapp.templates.index')
            ->with('success', "✅ Template \"{$nome}\" removido com sucesso!");
    }

    /**
     * ============================================================
     * TOGGLE ATIVO: Alterna entre ativo/inativo via AJAX
     * ============================================================
     */
    public function toggleAtivo($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);

        $this->autorizarAcesso($template);

        $template->is_active = !$template->is_active;
        $template->save();

        return response()->json([
            'success'   => true,
            'is_active' => $template->is_active,
            'message'   => $template->is_active
                ? '✅ Template ativado.'
                : '⚠️ Template desativado.',
        ]);
    }

    /**
     * ============================================================
     * PREVIEW JSON: Renderiza o template com valores de exemplo
     * ============================================================
     */
    public function previewJson($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);

        $this->autorizarAcesso($template);

        // Gerar valores de exemplo para as variáveis
        $exemplo = [];
        if (!empty($template->variaveis)) {
            foreach ($template->variaveis as $var) {
                $exemplo[$var] = '[exemplo_' . $var . ']';
            }
        }

        return response()->json([
            'sucesso'   => true,
            'renderizado' => $template->render($exemplo),
            'variaveis' => $template->variaveis,
        ]);
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    /**
     * Extrai as variáveis de uma mensagem (formato {{variavel}} ou {variavel})
     */
    protected function extrairVariaveis(string $mensagem): array
    {
        $variaveis = [];

        // Procurar por {{variavel}}
        if (preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $mensagem, $matches)) {
            $variaveis = array_merge($variaveis, $matches[1]);
        }

        // Procurar por {variavel} (formato simples, para não colidir com {{ }})
        $semDuplas = preg_replace('/\{\{\s*[a-zA-Z0-9_]+\s*\}\}/', '', $mensagem);
        if (preg_match_all('/\{\s*([a-zA-Z0-9_]+)\s*\}/', $semDuplas, $matches)) {
            $variaveis = array_merge($variaveis, $matches[1]);
        }

        return array_values(array_unique($variaveis));
    }

    /**
     * Autoriza o acesso a um template (multi-organizador)
     */
    protected function autorizarAcesso(WhatsAppTemplate $template): void
    {
        if (Auth::user()->isAdmin()) {
            return;
        }

        if ($template->organizacao_id !== Auth::user()->organizacao_id) {
            abort(403, 'Sem permissão para acessar este template.');
        }
    }
}