<?php

/**
 * ============================================================
 * CONTROLLER: ReembolsoController
 * ============================================================
 * 
 * Gerencia todas as operações relacionadas a reembolsos:
 * - Listar reembolsos do usuário
 * - Solicitar reembolso de ingresso
 * - Visualizar detalhes do reembolso
 * - Admin: Listar todos os reembolsos
 * - Admin: Aprovar reembolso
 * - Admin: Rejeitar reembolso
 * - Admin: Processar reembolso
 * 
 * Fluxo completo:
 * 1. Usuário solicita reembolso (status: pendente)
 * 2. Admin aprova ou rejeita
 * 3. Se aprovado, Admin processa (status: processado)
 * 4. Usuário recebe notificações em cada etapa
 * 
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Reembolso;
use App\Models\CarteiraIngresso;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ReembolsoController extends Controller
{
    /**
     * ============================================================
     * INDEX: Lista todos os reembolsos do usuário
     * ============================================================
     * 
     * @return \Illuminate\View\View
     * 
     * Mostra apenas os reembolsos do usuário autenticado.
     * Inclui relacionamentos para exibir dados do ingresso e evento.
     */
    public function index()
    {
        $reembolsos = Reembolso::where('user_id', Auth::id())
                               ->with(['carteiraIngresso', 'carteiraIngresso.ingresso.evento'])
                               ->orderBy('created_at', 'desc')
                               ->paginate(15);

        return view('admin.reembolsos.index', compact('reembolsos'));
    }

    /**
     * ============================================================
     * CREATE: Mostra o formulário para solicitar reembolso
     * ============================================================
     * 
     * @param int $carteiraId - ID do ingresso na carteira
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * 
     * Verifica se o ingresso pertence ao usuário, está ativo
     * e se já não existe uma solicitação pendente.
     */
    public function create($carteiraId)
    {
        // Buscar o ingresso na carteira do usuário
        $carteira = CarteiraIngresso::where('user_id', Auth::id())
                                    ->where('status', 'ativo')
                                    ->with(['ingresso', 'ingresso.evento'])
                                    ->findOrFail($carteiraId);

        // Verificar se já existe pedido de reembolso pendente
        $reembolsoExistente = Reembolso::where('carteira_ingresso_id', $carteiraId)
                                       ->where('status', 'pendente')
                                       ->exists();

        if ($reembolsoExistente) {
            return redirect()->route('carteira.index')
                ->with('error', 'Já existe uma solicitação de reembolso pendente para este ingresso.');
        }

        return view('admin.reembolsos.create', compact('carteira'));
    }

    /**
     * ============================================================
     * STORE: Salva uma solicitação de reembolso
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * 
     * Fluxo:
     * 1. Valida os dados
     * 2. Verifica se o ingresso pertence ao usuário
     * 3. Verifica se já existe solicitação pendente
     * 4. Cria o reembolso com status "pendente"
     * 5. Atualiza o status do ingresso na carteira para "cancelado"
     * 6. Redireciona com mensagem de sucesso
     */
    public function store(Request $request)
    {
        // ============================================
        // VALIDAÇÃO DOS DADOS
        // ============================================
        $request->validate([
            'carteira_id' => 'required|exists:carteira_ingressos,id',
            'motivo' => 'required|string|max:255',
            'observacoes' => 'nullable|string|max:500',
        ]);

        // ============================================
        // VERIFICAR SE O INGRESSO PERTENCE AO USUÁRIO
        // ============================================
        $carteira = CarteiraIngresso::where('user_id', Auth::id())
                                     ->where('status', 'ativo')
                                     ->findOrFail($request->carteira_id);

        // ============================================
        // VERIFICAR SE JÁ EXISTE SOLICITAÇÃO PENDENTE
        // ============================================
        $reembolsoExistente = Reembolso::where('carteira_ingresso_id', $carteira->id)
                                       ->where('status', 'pendente')
                                       ->exists();

        if ($reembolsoExistente) {
            return redirect()->back()
                ->with('error', 'Já existe uma solicitação de reembolso pendente para este ingresso.');
        }

        // ============================================
        // CRIAR O REEMBOLSO
        // ============================================
        $reembolso = Reembolso::create([
            'uuid' => (string) Str::uuid(),
            'carteira_ingresso_id' => $carteira->id,
            'user_id' => Auth::id(),
            'valor_reembolso' => $carteira->ingresso->preco_pago ?? 0,
            'motivo' => $request->motivo,
            'observacoes' => $request->observacoes,
            'status' => 'pendente',
            'data_solicitacao' => now(),
        ]);

        // ============================================
        // ATUALIZAR STATUS DO INGRESSO NA CARTEIRA
        // ============================================
        // O ingresso fica como "cancelado" enquanto aguarda aprovação
        $carteira->status = 'cancelado';
        $carteira->save();

        // ============================================
        // LOG DA OPERAÇÃO
        // ============================================
        Log::info('Reembolso solicitado: ' . $reembolso->id . 
                  ' - Usuário: ' . Auth::user()->email . 
                  ' - Valor: ' . $reembolso->valor_reembolso);

        // ============================================
        // REDIRECIONAR COM MENSAGEM DE SUCESSO
        // ============================================
        return redirect()->route('reembolsos.index')
            ->with('success', '✅ Solicitação de reembolso enviada com sucesso! Aguarde a aprovação.');
    }

    /**
     * ============================================================
     * SHOW: Exibe detalhes de um reembolso
     * ============================================================
     * 
     * @param int $id - ID do reembolso
     * @return \Illuminate\View\View
     * 
     * Mostra os detalhes completos de uma solicitação de reembolso.
     * Apenas o usuário que solicitou pode ver.
     */
    public function show($id)
    {
        $reembolso = Reembolso::where('user_id', Auth::id())
                              ->with([
                                  'carteiraIngresso',
                                  'carteiraIngresso.ingresso.evento',
                                  'carteiraIngresso.ingresso.categoria'
                              ])
                              ->findOrFail($id);

        return view('admin.reembolsos.show', compact('reembolso'));
    }

    /**
     * ============================================================
     * ADMIN INDEX: Lista todos os reembolsos (administrativo)
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     * 
     * Lista todos os reembolsos do sistema com filtros.
     * Ordena por status (pendentes primeiro).
     * Apenas administradores podem acessar.
     */
    public function adminIndex(Request $request)
    {
        // Verificar se o usuário é admin
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem acessar esta página.');
        }

        $query = Reembolso::with(['user', 'carteiraIngresso.ingresso.evento']);

        // Filtro por status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filtro por data
        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        // Ordenar: pendentes primeiro, depois por data
        $reembolsos = $query->orderByRaw("FIELD(status, 'pendente', 'aprovado', 'rejeitado', 'processado')")
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);

        return view('admin.reembolsos.admin-index', compact('reembolsos'));
    }

    /**
     * ============================================================
     * ADMIN APROVAR: Aprova um reembolso
     * ============================================================
     * 
     * @param int $id - ID do reembolso
     * @return \Illuminate\Http\JsonResponse
     * 
     * Fluxo:
     * 1. Verifica se o usuário é admin
     * 2. Verifica se o reembolso está pendente
     * 3. Atualiza status para "aprovado"
     * 4. Notifica o usuário
     * 5. Retorna JSON com sucesso
     */
    public function adminAprovar($id)
    {
        try {
            // Verificar se o usuário é admin
            if (!Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Apenas administradores podem realizar esta ação.'
                ], 403);
            }

            // Buscar o reembolso
            $reembolso = Reembolso::with(['user', 'carteiraIngresso'])
                                  ->findOrFail($id);

            // Verificar se está pendente
            if (!$reembolso->isPendente()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este reembolso não está pendente.'
                ], 400);
            }

            // Atualizar status
            $reembolso->status = 'aprovado';
            $reembolso->data_aprovacao = now();
            $reembolso->save();

            // Notificar o usuário
            Notificacao::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $reembolso->user_id,
                'titulo' => '✅ Reembolso aprovado',
                'mensagem' => 'Seu pedido de reembolso para o ingresso ' . 
                              $reembolso->carteiraIngresso->codigo_acesso . 
                              ' foi aprovado. Aguarde o processamento financeiro.',
                'tipo' => 'success',
                'link' => route('reembolsos.show', $reembolso->id),
                'lida' => false,
            ]);

            Log::info('Reembolso aprovado: ' . $reembolso->id . 
                      ' - Admin: ' . Auth::user()->email);

            return response()->json([
                'success' => true,
                'message' => '✅ Reembolso aprovado com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao aprovar reembolso: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aprovar reembolso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * ADMIN REJEITAR: Rejeita um reembolso
     * ============================================================
     * 
     * @param Request $request
     * @param int $id - ID do reembolso
     * @return \Illuminate\Http\JsonResponse
     * 
     * Fluxo:
     * 1. Verifica se o usuário é admin
     * 2. Verifica se o reembolso está pendente
     * 3. Atualiza status para "rejeitado"
     * 4. Restaura o ingresso na carteira para "ativo"
     * 5. Notifica o usuário
     * 6. Retorna JSON com sucesso
     */
    public function adminRejeitar(Request $request, $id)
    {
        try {
            // Verificar se o usuário é admin
            if (!Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Apenas administradores podem realizar esta ação.'
                ], 403);
            }

            // Validar motivo
            $request->validate([
                'motivo_rejeicao' => 'required|string|max:255',
            ]);

            // Buscar o reembolso
            $reembolso = Reembolso::with(['user', 'carteiraIngresso'])
                                  ->findOrFail($id);

            // Verificar se está pendente
            if (!$reembolso->isPendente()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este reembolso não está pendente.'
                ], 400);
            }

            // Atualizar status
            $reembolso->status = 'rejeitado';
            $reembolso->observacoes = $request->motivo_rejeicao;
            $reembolso->save();

            // Restaurar status do ingresso na carteira
            $carteira = $reembolso->carteiraIngresso;
            $carteira->status = 'ativo';
            $carteira->save();

            // Notificar o usuário
            Notificacao::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $reembolso->user_id,
                'titulo' => '❌ Reembolso rejeitado',
                'mensagem' => 'Seu pedido de reembolso foi rejeitado. Motivo: ' . $request->motivo_rejeicao,
                'tipo' => 'danger',
                'link' => route('reembolsos.show', $reembolso->id),
                'lida' => false,
            ]);

            Log::info('Reembolso rejeitado: ' . $reembolso->id . 
                      ' - Admin: ' . Auth::user()->email);

            return response()->json([
                'success' => true,
                'message' => '✅ Reembolso rejeitado com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao rejeitar reembolso: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao rejeitar reembolso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * ADMIN PROCESSAR: Processa um reembolso (pagamento financeiro)
     * ============================================================
     * 
     * @param int $id - ID do reembolso
     * @return \Illuminate\Http\JsonResponse
     * 
     * Fluxo:
     * 1. Verifica se o usuário é admin
     * 2. Verifica se o reembolso está aprovado
     * 3. Atualiza status para "processado"
     * 4. Notifica o usuário
     * 5. Retorna JSON com sucesso
     */
    public function adminProcessar($id)
    {
        try {
            // Verificar se o usuário é admin
            if (!Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Apenas administradores podem realizar esta ação.'
                ], 403);
            }

            // Buscar o reembolso
            $reembolso = Reembolso::with(['user', 'carteiraIngresso'])
                                  ->findOrFail($id);

            // Verificar se está aprovado
            if (!$reembolso->isAprovado()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este reembolso não está aprovado.'
                ], 400);
            }

            // Atualizar status
            $reembolso->status = 'processado';
            $reembolso->data_processamento = now();
            $reembolso->save();

            // Notificar o usuário
            Notificacao::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $reembolso->user_id,
                'titulo' => '💰 Reembolso processado',
                'mensagem' => 'O reembolso de ' . 
                              number_format($reembolso->valor_reembolso, 2, ',', '.') . 
                              ' MZN foi processado com sucesso.',
                'tipo' => 'success',
                'link' => route('reembolsos.show', $reembolso->id),
                'lida' => false,
            ]);

            Log::info('Reembolso processado: ' . $reembolso->id . 
                      ' - Admin: ' . Auth::user()->email . 
                      ' - Valor: ' . $reembolso->valor_reembolso);

            return response()->json([
                'success' => true,
                'message' => '✅ Reembolso processado com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar reembolso: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar reembolso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * TESTE: Método para testar se o controller está funcionando
     * ============================================================
     * 
     * @return string
     */
    public function test()
    {
        return 'Teste do ReembolsoController funcionando!';
    }
}