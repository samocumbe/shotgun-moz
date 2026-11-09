<?php

/**
 * ============================================================
 * CONTROLLER: WhatsAppWebhookController - SHOTGUN-MOZ
 * ============================================================
 *
 * Recebe notificações de status da Meta (WhatsApp Cloud API).
 *
 * Este endpoint é PÚBLICO (fora do middleware auth) e GRÁTIS.
 * A Meta chama-o sempre que uma mensagem muda de estado:
 *   enviado → entregue → lido
 * ou falha.
 *
 * Rotas:
 * - GET  /webhooks/whatsapp  → verificação inicial (hub.challenge)
 * - POST /webhooks/whatsapp  → receção de eventos (status update)
 *
 * Segurança:
 * - Se WHATSAPP_APP_SECRET estiver configurado, valida a assinatura
 *   X-Hub-Signature-256 enviada pela Meta.
 * - Em modo de teste (app_secret vazio), aceita todas as chamadas.
 *
 * Fluxo:
 * 1. Encontra a mensagem pela coluna `referencia` (= wamid.xxx)
 * 2. Atualiza o status + data_entrega / data_leitura
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * GET /webhooks/whatsapp
     * ---------------------------------------------------------
     * Verificação inicial da Meta (handshake).
     *
     * Quando configuras a Callback URL no Meta Business, a Meta
     * faz um GET com 3 parâmetros:
     *   - hub.mode         = "subscribe"
     *   - hub.verify_token = token que tu definiste
     *   - hub.challenge    = string aleatória que tens de devolver
     *
     * Se o token bater certo, devolve o challenge tal como veio.
     * Caso contrário, devolve 403.
     */
    public function verificar(Request $request)
    {
        $verifyToken = config('services.whatsapp.verify_token');

        $mode      = $request->input('hub_mode');
        $token     = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp webhook: verificação bem-sucedida');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook: token de verificação inválido', [
            'mode'  => $mode,
            'token' => $token,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * POST /webhooks/whatsapp
     * ---------------------------------------------------------
     * Recebe eventos de status (enviado, entregue, lido, falha).
     *
     * Payload típico da Meta:
     * {
     *   "object": "whatsapp_business_account",
     *   "entry": [{
     *     "id": "...",
     *     "changes": [{
     *       "value": {
     *         "messaging_product": "whatsapp",
     *         "statuses": [{
     *           "id": "wamid.xxx",
     *           "status": "delivered",
     *           "timestamp": "...",
     *           "recipient_id": "+258..."
     *         }]
     *       }
     *     }]
     *   }]
     * }
     */
    public function receber(Request $request)
    {
        // 1. Validar assinatura (só se app_secret estiver configurado)
        if (!$this->validarAssinatura($request)) {
            Log::warning('WhatsApp webhook: assinatura inválida');
            return response('Unauthorized', 401);
        }

        $payload = $request->all();

        Log::info('WhatsApp webhook recebido', ['payload' => $payload]);

        // 2. Processar eventos
        try {
            $entries = $payload['entry'] ?? [];

            foreach ($entries as $entry) {
                $changes = $entry['changes'] ?? [];

                foreach ($changes as $change) {
                    $value = $change['value'] ?? [];

                    // 2.1. Status updates (enviado/entregue/lido/falha)
                    if (isset($value['statuses'])) {
                        foreach ($value['statuses'] as $status) {
                            $this->processarStatus($status);
                        }
                    }

                    // 2.2. Mensagens recebidas (opcional - log apenas)
                    if (isset($value['messages'])) {
                        Log::info('WhatsApp webhook: mensagem recebida', $value['messages']);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao processar webhook WhatsApp', [
                'erro'  => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Sempre devolver 200 para a Meta não reenviar
        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Processa um evento de status individual.
     *
     * @param array $status Ex: [
     *     'id' => 'wamid.xxx',
     *     'status' => 'delivered',
     *     'timestamp' => '1234567890',
     *     'recipient_id' => '+258...'
     * ]
     */
    protected function processarStatus(array $status): void
    {
        $messageId  = $status['id']     ?? null;
        $statusTipo = $status['status'] ?? null;

        if (!$messageId || !$statusTipo) {
            return;
        }

        // Procurar a mensagem pela referência (wamid.xxx)
        $message = WhatsAppMessage::where('referencia', $messageId)->first();

        if (!$message) {
            Log::warning('WhatsApp webhook: mensagem não encontrada', [
                'referencia' => $messageId,
                'status'     => $statusTipo,
            ]);
            return;
        }

        // Mapear status da Meta para o nosso enum
        switch ($statusTipo) {
            case 'sent':
                // Já marcámos como 'enviado' quando saiu da fila.
                // Não fazer nada para não regredir.
                break;

            case 'delivered':
                $message->marcarComoEntregue();
                Log::info('WhatsApp: entregue', ['id' => $message->id]);
                break;

            case 'read':
                $message->marcarComoLido();
                Log::info('WhatsApp: lido', ['id' => $message->id]);
                break;

            case 'failed':
                $erro = $status['errors'][0]['title'] ?? 'Falha reportada pela Meta';
                $message->marcarComoFalha($erro);
                Log::warning('WhatsApp: falha', [
                    'id'   => $message->id,
                    'erro' => $erro,
                ]);
                break;
        }
    }

    /**
     * Valida a assinatura X-Hub-Signature-256 da Meta.
     *
     * A Meta assina o payload com HMAC-SHA256 usando o App Secret.
     * Se o App Secret não estiver configurado, aceita (modo de teste).
     */
    protected function validarAssinatura(Request $request): bool
    {
        $appSecret = config('services.whatsapp.app_secret');

        // Se não configurado, aceita (modo de testes local)
        if (empty($appSecret)) {
            return true;
        }

        $signature = $request->header('X-Hub-Signature-256');

        if (!$signature) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);

        return hash_equals($expected, $signature);
    }
}