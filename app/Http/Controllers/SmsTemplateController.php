<?php

/**
 * ============================================================
 * CONTROLLER: SmsTemplateController - SHOTGUN-MOZ
 * ============================================================
 *
 * Gere o CRUD completo dos templates de SMS.
 *
 * Um template é um modelo reutilizável de mensagem SMS que pode
 * conter variáveis no formato {variavel} ou {{variavel}}, que são
 * substituídas no momento do envio.
 *
 * Duas dimensões de classificação:
 * - tipo      → canal de envio: 'sms' | 'whatsapp'
 * - categoria → tipo de negócio: 'venda' | 'lembrete' | 'promocao' | 'evento' | 'outro'
 *
 * Multi-Organizador:
 * - Organizadores só vêem/gere os seus próprios templates
 * - Admin vê/gere todos
 *
 * Rotas associadas (ver routes/web.php):
 * - GET    sms/templates                → index
 * - GET    sms/templates/create         → create
 * - POST   sms/templates                → store
 * - GET    sms/templates/{id}           → show
 * - GET    sms/templates/{id}/edit      → edit
 * - PUT    sms/templates/{id}           → update
 * - DELETE sms/templates/{id}           → destroy
 * - POST   sms/templates/{id}/toggle-ativo → toggleAtivo
 * - GET    sms/templates/{id}/render-json  → renderJson
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SmsTemplateController extends Controller
{
    /**
     * ============================================================
     * INDEX: Lista todos os templates
     * ============================================================
     *
     * Filtros suportados:
     * - tipo      (sms | whatsapp)
     * - categoria (venda | lembrete | promocao | evento | outro)
     * - is_active (0 | 1)
     * - busca     (pesquisa por nome)
     *
     * Multi-Organizador:
     * - Admin vê todos
     * - Utilizador normal vê apenas os da sua organização
     */
    public function index(Request $request)
    {
        $query = SmsTemplate::with(['organizacao']);

        // ---- Filtro por tipo (canal) ----
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // ---- Filtro por categoria (novo) ----
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        // ---- Filtro por estado (ativo/inativo) ----
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        // ---- Pesquisa por nome ----
        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        // ---- Multi-Organizador ----
        if (!Auth::user()->isAdmin() && Auth::user()->organizacao_id) {
            $query->where(function ($q) {
                $q->where('organizacao_id', Auth::user()->organizacao_id)
                  ->orWhereNull('organizacao_id');
            });
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(20);

        // Listas para os <select> dos filtros na view
        $tipos      = SmsTemplate::getTipos();
        $categorias = SmsTemplate::getCategorias();

        return view('admin.sms.templates.index', compact('templates', 'tipos', 'categorias'));
    }

    /**
     * ============================================================
     * CREATE: Formulário de criação
     * ============================================================
     *
     * Envia para a view as listas de canais (tipos) e categorias,
     * para preencher os <select>.
     */
    public function create()
    {
        $tipos      = SmsTemplate::getTipos();
        $categorias = SmsTemplate::getCategorias();

        return view('admin.sms.templates.create', compact('tipos', 'categorias'));
    }

    /**
     * ============================================================
     * STORE: Guarda um novo template
     * ============================================================
     *
     * Validações:
     * - nome       (obrigatório, máx 100)
     * - assunto    (opcional, máx 100)
     * - mensagem   (obrigatório, máx 480)
     * - tipo       (obrigatório, no conjunto TIPOS)
     * - categoria  (obrigatório, no conjunto CATEGORIAS)
     * - is_active  (booleano, opcional)
     *
     * As variáveis {x} e {{x}} são extraídas automaticamente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome'      => 'required|string|max:100',
            'assunto'   => 'nullable|string|max:100',
            'mensagem'  => 'required|string|max:480',
            'tipo'      => 'required|in:' . implode(',', array_keys(SmsTemplate::TIPOS)),
            'categoria' => 'required|in:' . implode(',', array_keys(SmsTemplate::CATEGORIAS)),
            'is_active' => 'nullable|boolean',
        ]);

        $template = SmsTemplate::create([
            'uuid'           => (string) Str::uuid(),
            'nome'           => $request->nome,
            'assunto'        => $request->assunto,
            'mensagem'       => $request->mensagem,
            'tipo'           => $request->tipo,              // canal
            'categoria'      => $request->categoria,         // negócio
            'is_active'      => $request->boolean('is_active', true),
            'organizacao_id' => Auth::user()->organizacao_id,
            'variaveis'      => SmsTemplate::extrairVariaveisDe($request->mensagem),
        ]);

        return redirect()
            ->route('sms.templates.index')
            ->with('success', "Template \"{$template->nome}\" criado com sucesso!");
    }

    /**
     * ============================================================
     * SHOW: Detalhes de um template
     * ============================================================
     *
     * Multi-Organizador: valida permissão antes de mostrar.
     */
    public function show($id)
    {
        $template = SmsTemplate::with(['organizacao', 'sms'])
                               ->findOrFail($id);

        // Verificação de permissão
        if (!Auth::user()->isAdmin()
            && $template->organizacao_id
            && $template->organizacao_id !== Auth::user()->organizacao_id) {
            abort(403, 'Sem permissão.');
        }

        // Lista de variáveis detetadas no template
        $variaveis = $template->extrairVariaveis();

        return view('admin.sms.templates.show', compact('template', 'variaveis'));
    }

    /**
     * ============================================================
     * EDIT: Formulário de edição
     * ============================================================
     */
    public function edit($id)
    {
        $template = SmsTemplate::findOrFail($id);

        if (!Auth::user()->isAdmin()
            && $template->organizacao_id
            && $template->organizacao_id !== Auth::user()->organizacao_id) {
            abort(403, 'Sem permissão.');
        }

        $tipos      = SmsTemplate::getTipos();
        $categorias = SmsTemplate::getCategorias();

        return view('admin.sms.templates.edit', compact('template', 'tipos', 'categorias'));
    }

    /**
     * ============================================================
     * UPDATE: Atualiza um template existente
     * ============================================================
     *
     * Mesmas regras de validação que o store, mas com a extração
     * de variáveis novamente a partir da mensagem atualizada.
     */
    public function update(Request $request, $id)
    {
        $template = SmsTemplate::findOrFail($id);

        if (!Auth::user()->isAdmin()
            && $template->organizacao_id
            && $template->organizacao_id !== Auth::user()->organizacao_id) {
            abort(403, 'Sem permissão.');
        }

        $request->validate([
            'nome'      => 'required|string|max:100',
            'assunto'   => 'nullable|string|max:100',
            'mensagem'  => 'required|string|max:480',
            'tipo'      => 'required|in:' . implode(',', array_keys(SmsTemplate::TIPOS)),
            'categoria' => 'required|in:' . implode(',', array_keys(SmsTemplate::CATEGORIAS)),
            'is_active' => 'nullable|boolean',
        ]);

        $template->update([
            'nome'      => $request->nome,
            'assunto'   => $request->assunto,
            'mensagem'  => $request->mensagem,
            'tipo'      => $request->tipo,
            'categoria' => $request->categoria,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Re-extrair variáveis a partir da mensagem atualizada
        $template->variaveis = SmsTemplate::extrairVariaveisDe($request->mensagem);
        $template->save();

        return redirect()
            ->route('sms.templates.index')
            ->with('success', 'Template atualizado com sucesso!');
    }

    /**
     * ============================================================
     * DESTROY: Remove um template
     * ============================================================
     *
     * Nota: templates associados a SMS enviados podem ficar órfãos.
     * Se quiseres impedir a remoção, descomenta a verificação abaixo.
     */
    public function destroy($id)
    {
        $template = SmsTemplate::findOrFail($id);

        if (!Auth::user()->isAdmin()
            && $template->organizacao_id
            && $template->organizacao_id !== Auth::user()->organizacao_id) {
            abort(403, 'Sem permissão.');
        }

        // 🛡️ Exemplo (opcional): impedir remoção se houver SMS associados
        // if ($template->sms()->exists()) {
        //     return redirect()->back()
        //         ->with('error', 'Não é possível remover: existem SMS enviados com este template.');
        // }

        $nome = $template->nome;
        $template->delete();

        return redirect()
            ->route('sms.templates.index')
            ->with('success', "Template \"{$nome}\" removido com sucesso!");
    }

    /**
     * ============================================================
     * TOGGLE ATIVO: Alterna entre ativo/inativo (AJAX)
     * ============================================================
     *
     * Retorna JSON com o novo estado, para o frontend atualizar
     * o switch sem recarregar a página.
     */
    public function toggleAtivo($id)
    {
        $template = SmsTemplate::findOrFail($id);

        if (!Auth::user()->isAdmin()
            && $template->organizacao_id
            && $template->organizacao_id !== Auth::user()->organizacao_id) {
            return response()->json([
                'success' => false,
                'message' => 'Sem permissão',
            ], 403);
        }

        $template->is_active = !$template->is_active;
        $template->save();

        return response()->json([
            'success'   => true,
            'is_active' => $template->is_active,
            'message'   => $template->is_active
                ? 'Template ativado.'
                : 'Template desativado.',
        ]);
    }

    /**
     * ============================================================
     * RENDER JSON: Devolve a mensagem renderizada (AJAX)
     * ============================================================
     *
     * Útil para pré-visualizar o template com valores dinâmicos
     * enviados pelo frontend.
     *
     * Exemplo de chamada:
     * GET /admin/sms/templates/{id}/render-json?dados[nome]=João&dados[valor]=500
     *
     * Resposta:
     * {
     *   "success": true,
     *   "conteudo": "Olá João, ... 500 MT.",
     *   "variaveis": ["nome", "valor", ...]
     * }
     */
    public function renderJson($id, Request $request)
    {
        $template = SmsTemplate::findOrFail($id);

        if (!Auth::user()->isAdmin()
            && $template->organizacao_id
            && $template->organizacao_id !== Auth::user()->organizacao_id) {
            return response()->json([
                'success' => false,
                'message' => 'Sem permissão',
            ], 403);
        }

        // Dados opcionais para substituir as variáveis
        $dados = $request->input('dados', []);

        $conteudo = $template->renderizar($dados);

        return response()->json([
            'success'   => true,
            'conteudo'  => $conteudo,
            'variaveis' => $template->extrairVariaveis(),
        ]);
    }
}