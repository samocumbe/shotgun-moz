<?php

/**
 * ============================================================
 * SERVICE: WhatsAppService - SHOTGUN-MOZ
 * ============================================================
 *
 * Serviço central de envio de mensagens WhatsApp.
 * Abstrai o provedor real (Meta, Twilio, WATI, etc).
 *
 * Drivers suportados (configuráveis em config/services.php):
 * - log    → apenas registra no log (ideal para testes)
 * - twilio → Twilio WhatsApp API
 * - meta   → WhatsApp Business API (Meta oficial)
 * - wati   → WATI (popular em Moçambique)
 *
 * Uso típico:
 * ```php
 * $service = app(WhatsAppService::class);
 * $resultado = $service->enviarWhatsApp('+258841234567', 'Olá!', [
 *     'evento_id' => 1,
 *     'template_id' => 5,
 * ]);
 * ```
 *
 * Retorno padrão:
 * ```php
 * [
 *     'success' => true|false,
 *     'message' => 'Mensagem de sucesso ou erro',
 *     'id'      => 123,
 * ]
 * ```
 */

namespace App\Services;

use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $driver;
    protected ?string $apiKey;
    protected ?string $apiUrl;
    protected ?string $phoneId;

    public function __construct()
    {
        $this->driver  = config('services.whatsapp.driver', 'log');
        $this->apiKey  = config('services.whatsapp.api_key');
        $this->apiUrl  = config('services.whatsapp.api_url');
        $this->phoneId = config('services.whatsapp.phone_id');
    }

    // ============================================================
    // MÉTODO PRINCIPAL
    // ============================================================

    /**
     * Envia uma mensagem WhatsApp
     *
     * @param string $telefone Número de destino (aceita vários formatos)
     * @param string $mensagem Conteúdo da mensagem
     * @param array  $dados    Dados opcionais:
     *                         - evento_id (int)
     *                         - template_id (int|string)
     *                         - lote_id (string)
     *
     * @return array{success: bool, message: string, id?: int}
     */
    public function enviarWhatsApp(string $telefone, string $mensagem, array $dados = []): array
    {
        try {
            $telefone = $this->normalizarTelefone($telefone);

            // Registar no banco
            $whatsapp = WhatsAppMessage::create([
                'telefone'       => $telefone,
                'mensagem'       => $mensagem,
                'user_id'        => auth()->id(),
                'organizacao_id' => auth()->user()->organizacao_id ?? null,
                'evento_id'      => $dados['evento_id'] ?? null,
                'template_id'    => $dados['template_id'] ?? null,
                'referencia'     => $dados['lote_id'] ?? null,
                'status'         => 'pendente',
                'provedor'       => $this->driver,
            ]);

            // Escolher driver
            $enviado = match ($this->driver) {
                'twilio' => $this->enviarTwilio($whatsapp),
                'meta'   => $this->enviarMeta($whatsapp),
                'wati'   => $this->enviarWati($whatsapp),
                'log'    => $this->enviarLog($whatsapp),
                default  => throw new \Exception("Driver WhatsApp não suportado: {$this->driver}"),
            };

            if ($enviado) {
                $whatsapp->marcarComoEnviado();

                return [
                    'success' => true,
                    'message' => 'Mensagem WhatsApp enviada com sucesso!',
                    'id'      => $whatsapp->id,
                ];
            }

            $whatsapp->marcarComoFalha('Falha no envio pelo provedor');

            return [
                'success' => false,
                'message' => 'Falha ao enviar WhatsApp pelo provedor.',
                'id'      => $whatsapp->id,
            ];

        } catch (\Throwable $e) {
            Log::error('Erro ao enviar WhatsApp', [
                'telefone' => $telefone ?? null,
                'driver'   => $this->driver,
                'erro'     => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao enviar WhatsApp: ' . $e->getMessage(),
            ];
        }
    }

    // ============================================================
    // NORMALIZAÇÃO DE TELEFONE
    // ============================================================

    /**
     * Normaliza o telefone para formato internacional (+258XXXXXXXXX)
     */
    protected function normalizarTelefone(string $telefone): string
    {
        $telefone = preg_replace('/\D/', '', $telefone);

        if (str_starts_with($telefone, '00')) {
            $telefone = substr($telefone, 2);
        }

        if (!str_starts_with($telefone, '258')) {
            $telefone = '258' . ltrim($telefone, '0');
        }

        return '+' . $telefone;
    }

    // ============================================================
    // DRIVERS DE ENVIO
    // ============================================================

    /**
     * Driver LOG (teste)
     */
    protected function enviarLog(WhatsAppMessage $message): bool
    {
        Log::info("[WHATSAPP-LOG] Para: {$message->telefone} | Msg: {$message->mensagem}");

        return true;
    }

    /**
     * Driver Twilio WhatsApp
     */
    protected function enviarTwilio(WhatsAppMessage $message): bool
    {
        if (empty($this->apiKey) || empty($this->apiUrl)) {
            throw new \Exception('Credenciais Twilio WhatsApp não configuradas.');
        }

        // Exemplo (descomentar quando tiver credenciais):
        //
        // $response = Http::withBasicAuth($this->phoneId, $this->apiKey)
        //     ->asForm()
        //     ->post("{$this->apiUrl}/Messages.json", [
        //         'From' => 'whatsapp:' . $this->phoneId,
        //         'To'   => 'whatsapp:' . $message->telefone,
        //         'Body' => $message->mensagem,
        //     ]);
        //
        // if ($response->successful()) {
        //     $message->referencia = $response->json('sid');
        //     $message->save();
        //     return true;
        // }
        //
        // return false;

        throw new \Exception('Integração Twilio WhatsApp ainda não implementada.');
    }

    /**
     * Driver Meta (WhatsApp Business API oficial - Cloud API).
     *
     * Envia a mensagem via Graph API e guarda o wamid (ID da Meta)
     * na coluna `referencia`, para que o webhook consiga depois
     * encontrar a mensagem e atualizar o status.
     *
     * Documentação: https://developers.facebook.com/docs/whatsapp/cloud-api
     */
    protected function enviarMeta(WhatsAppMessage $message): bool
    {
        if (empty($this->apiKey) || empty($this->phoneId)) {
            throw new \Exception('Credenciais Meta WhatsApp não configuradas (api_key, phone_id).');
        }

        // Endpoint da Graph API
        $url = rtrim($this->apiUrl ?: 'https://graph.facebook.com/v18.0', '/')
             . "/{$this->phoneId}/messages";

        // Payload (texto simples)
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $message->telefone,
            'type'              => 'text',
            'text'              => [
                'preview_url' => false,
                'body'        => $message->mensagem,
            ],
        ];

        Log::info('Meta WhatsApp: enviando', [
            'url'      => $url,
            'to'       => $message->telefone,
            'preview'  => mb_substr($message->mensagem, 0, 80),
        ]);

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post($url, $payload);

        if (!$response->successful()) {
            Log::error('Meta WhatsApp: falha na API', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \Exception(
                'Erro Meta WhatsApp (' . $response->status() . '): ' . $response->body()
            );
        }

        // Guardar o wamid (message id) para o webhook encontrar depois
        $wamid = $response->json('messages.0.id') ?? null;

        if ($wamid) {
            $message->referencia = $wamid;
            $message->save();

            Log::info('Meta WhatsApp: enviado', [
                'id'        => $message->id,
                'referencia' => $wamid,
            ]);
        }

        return true;
    }

    /**
     * Driver WATI (popular em Moçambique)
     */
    protected function enviarWati(WhatsAppMessage $message): bool
    {
        if (empty($this->apiKey) || empty($this->apiUrl)) {
            throw new \Exception('Credenciais WATI não configuradas.');
        }

        // Exemplo (descomentar quando tiver credenciais):
        //
        // $response = Http::withToken($this->apiKey)->post($this->apiUrl, [
        //     'whatsappNumber' => $message->telefone,
        //     'messageText'    => $message->mensagem,
        // ]);
        //
        // if ($response->successful()) {
        //     $message->referencia = $response->json('messageId') ?? null;
        //     $message->save();
        //     return true;
        // }
        //
        // return false;

        throw new \Exception('Integração WATI ainda não implementada.');
    }

    // ============================================================
    // MÉTODO AUXILIAR
    // ============================================================

    public function testarConfiguracao(): array
    {
        return [
            'driver'   => $this->driver,
            'api_key'  => $this->apiKey ? '✓ configurado' : '✗ em falta',
            'api_url'  => $this->apiUrl ?? 'não definido',
            'phone_id' => $this->phoneId ?? 'não definido',
        ];
    }
}