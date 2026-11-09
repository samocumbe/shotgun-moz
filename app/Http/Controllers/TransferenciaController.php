<?php

/**
 * ============================================================
 * CONTROLLER: TransferenciaController
 * ============================================================
 * 
 * Gerencia todas as operações relacionadas a transferências de ingressos:
 * - Criar solicitação de transferência
 * - Listar histórico de transferências
 * - Confirmar transferência (destinatário)
 * - Processar confirmação
 * 
 * Fluxo completo:
 * 1. Usuário A solicita transferência para Usuário B
 * 2. Sistema cria registro com status "pendente"
 * 3. Usuário B recebe notificação
 * 4. Usuário B confirma com código
 * 5. Sistema transfere o ingresso
 * 
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\CarteiraIngresso;
use App\Models\User;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TransferenciaController extends Controller
{
    /**
     * ============================================================
     * CREATE: Exibe o formulário para criar uma transferência
     * ============================================================
     * 
     * @param int $carteiraId - ID do ingresso na carteira
     * @return \Illuminate\View\View
     * 
     * Verifica se o ingresso pertence ao usuário logado
     * e exibe o formulário para enviar para outro usuário.
     */
    public function create($carteiraId)
    {
        // Buscar o ingresso na carteira do usuário autenticado
        $carteira = CarteiraIngresso::where('user_id', Auth::id())
                                    ->with(['ingresso', 'ingresso.evento'])
                                    ->findOrFail($carteiraId);
        
        return view('admin.transferencias.create', compact('carteira'));
    }

    /**
     * ============================================================
     * STORE: Cria uma nova solicitação de transferência
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * 
     * Fluxo:
     * 1. Valida os dados
     * 2. Verifica se o ingresso pertence ao usuário
     * 3. Verifica se o destinatário existe
     * 4. Cria a transferência com status "pendente"
     * 5. NÃO altera o status da carteira (só após confirmação)
     * 6. Notifica o destinatário
     * 7. Redireciona com mensagem de sucesso
     */
    public function store(Request $request)
    {
        // ============================================
        // VALIDAÇÃO DOS DADOS
        // ============================================
        $request->validate([
            'carteira_id' => 'required|exists:carteira_ingressos,id',
            'email_destino' => 'required|email|exists:users,email|different:email,' . Auth::user()->email,
        ]);

        // ============================================
        // VERIFICAR SE O INGRESSO PERTENCE AO USUÁRIO E ESTÁ ATIVO
        // ============================================
        $carteira = CarteiraIngresso::where('user_id', Auth::id())
                                     ->where('status', 'ativo')
                                     ->findOrFail($request->carteira_id);

        // ============================================
        // BUSCAR O USUÁRIO DESTINATÁRIO
        // ============================================
        $usuarioDestino = User::where('email', $request->email_destino)->first();

        // ============================================
        // CRIAR A TRANSFERÊNCIA (STATUS: PENDENTE)
        // ============================================
        $transferencia = Transferencia::create([
            'uuid' => (string) Str::uuid(),
            'carteira_ingresso_id' => $carteira->id,
            'usuario_origem_id' => Auth::id(),
            'usuario_destino_id' => $usuarioDestino->id,
            'status' => 'pendente',
        ]);

        // ============================================
        // IMPORTANTE: NÃO ALTERAR O STATUS DA CARTEIRA AQUI!
        // A carteira só será marcada como "transferido" após a confirmação
        // ============================================
        // ❌ REMOVIDO: $carteira->status = CarteiraIngresso::STATUS_TRANSFERIDO;
        // ❌ REMOVIDO: $carteira->data_transferencia = now();
        // ❌ REMOVIDO: $carteira->save();

        // ============================================
        // CRIAR NOTIFICAÇÃO PARA O DESTINATÁRIO
        // ============================================
        Notificacao::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $usuarioDestino->id,
            'titulo' => '🎫 Transferência de ingresso pendente',
            'mensagem' => 'Você recebeu uma transferência de ingresso de ' . Auth::user()->name . '. Código: ' . $transferencia->codigo_confirmacao,
            'tipo' => 'info',
            'link' => route('transferencias.confirmar', $transferencia->id),
            'lida' => false,
        ]);

        // ============================================
        // LOG DA OPERAÇÃO
        // ============================================
        Log::info('Transferência criada: ' . $transferencia->id . 
                  ' - De: ' . Auth::user()->email . 
                  ' - Para: ' . $usuarioDestino->email);

        // ============================================
        // REDIRECIONAR COM MENSAGEM DE SUCESSO
        // ============================================
        return redirect()->route('carteira.index')
            ->with('success', '✅ Transferência iniciada! O destinatário foi notificado e aguarda confirmação.');
    }

    /**
     * ============================================================
     * CONFIRMAR: Exibe a página de confirmação para o destinatário
     * ============================================================
     * 
     * @param int $id - ID da transferência
     * @return \Illuminate\View\View
     * 
     * Verifica se o usuário logado é o destinatário
     * e se a transferência está pendente.
     */
    public function confirmar($id)
    {
        // Buscar a transferência (apenas pendentes e para o usuário atual)
        $transferencia = Transferencia::with([
            'carteiraIngresso.ingresso.evento',
            'usuarioOrigem',
            'usuarioDestino'
        ])
        ->where('usuario_destino_id', Auth::id())
        ->where('status', 'pendente')
        ->findOrFail($id);

        return view('admin.transferencias.confirmar', compact('transferencia'));
    }

    /**
     * ============================================================
     * CONFIRMAR STORE: Processa a confirmação da transferência
     * ============================================================
     * 
     * @param Request $request
     * @param int $id - ID da transferência
     * @return \Illuminate\Http\RedirectResponse
     * 
     * Fluxo completo da confirmação:
     * 1. Valida o código de confirmação
     * 2. Verifica se a transferência está pendente
     * 3. Verifica se o código é válido
     * 4. Marca a carteira do remetente como "transferido"
     * 5. Marca a transferência como "confirmado"
     * 6. Cria uma nova carteira para o destinatário
     * 7. Notifica o remetente
     * 8. Redireciona com mensagem de sucesso
     */
    public function confirmarStore(Request $request, $id)
    {
        // ============================================
        // VALIDAÇÃO DO CÓDIGO
        // ============================================
        $request->validate([
            'codigo_confirmacao' => 'required|string|exists:transferencias,codigo_confirmacao',
        ]);

        // ============================================
        // BUSCAR A TRANSFERÊNCIA PENDENTE
        // ============================================
        $transferencia = Transferencia::where('usuario_destino_id', Auth::id())
                                      ->where('status', 'pendente')
                                      ->findOrFail($id);

        // ============================================
        // VERIFICAR SE O CÓDIGO ESTÁ CORRETO
        // ============================================
        if ($transferencia->codigo_confirmacao !== $request->codigo_confirmacao) {
            return redirect()->back()
                ->with('error', '❌ Código de confirmação inválido!')
                ->withInput();
        }

        // ============================================
        // BUSCAR A CARTEIRA DO REMETENTE
        // ============================================
        $carteira = $transferencia->carteiraIngresso;

        // ============================================
        // VERIFICAR SE A CARTEIRA AINDA ESTÁ ATIVA
        // ============================================
        if ($carteira->status !== CarteiraIngresso::STATUS_ATIVO) {
            return redirect()->back()
                ->with('error', '❌ Este ingresso não está mais disponível para transferência.');
        }

        // ============================================
        // 1. MARCAR A CARTEIRA DO REMETENTE COMO "TRANSFERIDO"
        // ============================================
        $carteira->status = CarteiraIngresso::STATUS_TRANSFERIDO;
        $carteira->data_transferencia = now();
        $carteira->save();

        // ============================================
        // 2. MARCAR A TRANSFERÊNCIA COMO "CONFIRMADO"
        // ============================================
        $transferencia->status = 'confirmado';
        $transferencia->data_confirmacao = now();
        $transferencia->save();

        // ============================================
        // 3. CRIAR UMA NOVA CARTEIRA PARA O DESTINATÁRIO
        // ============================================
        $novaCarteira = CarteiraIngresso::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => Auth::id(), // ID do destinatário
            'ingresso_id' => $carteira->ingresso_id,
            'codigo_acesso' => 'ACC-' . strtoupper(Str::random(6)) . '-' . strtoupper(Str::random(4)),
            'status' => CarteiraIngresso::STATUS_ATIVO,
            'data_ativacao' => now(),
        ]);

        // ============================================
        // LOG DA OPERAÇÃO
        // ============================================
        Log::info('Ingresso transferido: ' . $carteira->id . 
                  ' - Novo dono: ' . Auth::user()->email . 
                  ' - Nova carteira ID: ' . $novaCarteira->id);

        // ============================================
        // NOTIFICAR O REMETENTE
        // ============================================
        Notificacao::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $transferencia->usuario_origem_id,
            'titulo' => '✅ Transferência confirmada',
            'mensagem' => 'O ingresso foi transferido com sucesso para ' . Auth::user()->name,
            'tipo' => 'success',
            'link' => route('carteira.index'),
            'lida' => false,
        ]);

        // ============================================
        // REDIRECIONAR COM MENSAGEM DE SUCESSO
        // ============================================
        return redirect()->route('carteira.index')
            ->with('success', '✅ Ingresso transferido com sucesso! Verifique sua carteira.');
    }

    /**
     * ============================================================
     * HISTORICO: Lista todas as transferências do usuário
     * ============================================================
     * 
     * @return \Illuminate\View\View
     * 
     * Mostra transferências onde o usuário é remetente OU destinatário.
     * Inclui relacionamentos para exibir detalhes completos.
     */
    public function historico()
    {
        $transferencias = Transferencia::where('usuario_origem_id', Auth::id())
                                       ->orWhere('usuario_destino_id', Auth::id())
                                       ->with([
                                           'carteiraIngresso.ingresso.evento',
                                           'usuarioOrigem',
                                           'usuarioDestino'
                                       ])
                                       ->orderBy('created_at', 'desc')
                                       ->paginate(15);

        return view('admin.transferencias.historico', compact('transferencias'));
    }
}