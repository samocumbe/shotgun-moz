<?php

/**
 * ============================================================
 * SERVICE: SmsService - SHOTGUN-MOZ
 * ============================================================
 *
 * Serviço central de envio de SMS. Abstrai o provedor real
 * (Twilio, Africa's Talking, etc.) e oferece uma API simples
 * para o resto da aplicação.
 *
 * Drivers suportados (configurados em config/services.php):
 * - log            → apenas registra no log do Laravel (ideal para testes)
 * - twilio         → Twilio API
 * - africastalking → Africa's Talking (popular em Moçambique)
 *
 * Uso típico:
 * ```php
 * $smsService = app(SmsService::class);
 * $resultado  = $smsService->enviarSms('+258841234567', 'Olá!', [
 *     'evento_id'   => 1,
 *     'template_id' => 5,           // 🆕 template usado
 *     'lote_id'     => 'uuid-do-lote',
 * ]);
 * ```
 *
 * Retorno padrão:
 * ```php
 * [
 *     'success' => true|false,
 *     'message' => 'Mensagem de sucesso ou erro',
 *     'id'      => 123,             // ID do registo Sms criado
 * ]
 * ```
 *
 * Integração com envio em massa:
 * - Quando recebe `lote_id` em $dados, guarda-o em `referencia`.
 * - Quando recebe `template_id`, guarda-o na coluna `template_id`.
 * - Usado pelo SmsMassaController e pelo Job EnviarSmsJob.
 */

namespace App\Services;

use App\Models\Sms;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /** Driver em uso */
    protected string $driver;

    /** API Key do provedor */
    protected ?string $apiKey;

    /** URL base da API do provedor */
    protected ?string $apiUrl;

    /** Nome do remetente (ex: SHOTGUN) */
    protected ?string $sender;

    /** Username (usado por Africa's Talking) */
    protected ?string $username;

    /**
     * Construtor: carrega a configuração do ficheiro config/services.php
     */
    public function __construct()
    {
        $this->driver   = config('services.sms.driver', 'log');
        $this->apiKey   = config('services.sms.api_key');
        $this->apiUrl   = config('services.sms.api_url');
        $this->sender   = config('services.sms.sender', 'SHOTGUN');
        $this->username = config('services.sms.username');
    }

    // ============================================================
    // MÉTODO PRINCIPAL
    // ============================================================

    /**
     * Envia um SMS
     *
     * Fluxo:
     * 1. Normaliza o telefone para +258...
     * 2. Cria um registo na tabela `sms` com status "pendente"
     * 3. Chama o driver configurado (log | twilio | africastalking)
     * 4. Atualiza o estado para "enviado" ou "falha"
     * 5. Devolve array padronizado com o resultado
     *
     * @param string $telefone  Número de destino
     * @param string $mensagem  Conteúdo do SMS (máx. 480 chars)
     * @param array  $dados     Dados opcionais:
     *                          - evento_id   (int)
     *                          - template_id (int)   🆕
     *                          - lote_id     (string)
     *
     * @return array{success: bool, message: string, id?: int}
     */
    public function enviarSms(string $telefone, string $mensagem, array $dados = []): array
    {
        try {
            // 1. Normalizar telefone
            $telefone = $this->normalizarTelefone($telefone);

            // 2. Registar SMS no banco como "pendente"
            $sms = Sms::create([
                'telefone'       => $telefone,
                'mensagem'       => $mensagem,
                'user_id'        => auth()->id(),
                'organizacao_id' => auth()->user()->organizacao_id ?? null,
                'evento_id'      => $dados['evento_id']   ?? null,
                'template_id'    => $dados['template_id'] ?? null,   // 🆕
                'referencia'     => $dados['lote_id']     ?? null,
                'status'         => 'pendente',
                'provedor'       => $this->driver,
            ]);

            // 3. Escolher driver de envio
            $enviado = match ($this->driver) {
                'twilio'         => $this->enviarTwilio($sms),
                'africastalking' => $this->enviarAfricasTalking($sms),
                'log'            => $this->enviarLog($sms),
                default          => throw new \Exception("Driver SMS não suportado: {$this->driver}"),
            };

            // 4. Atualizar estado
            if ($enviado) {
                $sms->marcarComoEnviado();

                return [
                    'success' => true,
                    'message' => 'SMS enviado com sucesso!',
                    'id'      => $sms->id,
                ];
            }

            $sms->marcarComoFalha('Falha no envio pelo provedor');

            return [
                'success' => false,
                'message' => 'Falha ao enviar SMS pelo provedor.',
                'id'      => $sms->id,
            ];

        } catch (\Throwable $e) {
            Log::error('Erro ao enviar SMS', [
                'telefone' => $telefone ?? null,
                'driver'   => $this->driver,
                'erro'     => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao enviar SMS: ' . $e->getMessage(),
            ];
        }
    }

    // ============================================================
    // NORMALIZAÇÃO DE TELEFONE
    // ============================================================

    /**
     * Normaliza o número para formato internacional de Moçambique (+258XXXXXXXXX)
     *
     * Aceita:
     * - "841234567"      → "+258841234567"
     * - "84 123 4567"    → "+258841234567"
     * - "258841234567"   → "+258841234567"
     * - "+258841234567"  → "+258841234567"
     * - "00258841234567" → "+258841234567"
     *
     * @param string $telefone
     * @return string
     */
    protected function normalizarTelefone(string $telefone): string
    {
        // Remover tudo o que não seja dígito
        $telefone = preg_replace('/\D/', '', $telefone);

        // Remover prefixo internacional 00
        if (str_starts_with($telefone, '00')) {
            $telefone = substr($telefone, 2);
        }

        // Adicionar código do país se não existir
        if (!str_starts_with($telefone, '258')) {
            // Remover zero inicial (ex: 0841234567 → 841234567)
            $telefone = '258' . ltrim($telefone, '0');
        }

        return '+' . $telefone;
    }

    // ============================================================
    // DRIVERS DE ENVIO
    // ============================================================

    /**
     * Driver LOG (modo de teste)
     *
     * Não envia SMS real. Apenas registra no ficheiro de log
     * do Laravel (storage/logs/laravel.log).
     *
     * Ideal para: desenvolvimento local, testes, demonstrações.
     *
     * @param Sms $sms
     * @return bool  Sempre true (simula sucesso)
     */
    protected function enviarLog(Sms $sms): bool
    {
        Log::info("[SMS-LOG] Para: {$sms->telefone} | Msg: {$sms->mensagem}");

        return true;
    }

    /**
     * Driver Twilio (https://www.twilio.com/)
     *
     * Requer em config/services.php:
     * - services.sms.api_key   (Auth Token)
     * - services.sms.username  (Account SID)
     * - services.sms.sender    (número Twilio, ex: +1234567890)
     *
     * @param Sms $sms
     * @return bool
     * @throws \Exception
     */
    protected function enviarTwilio(Sms $sms): bool
    {
        if (empty($this->apiKey) || empty($this->username) || empty($this->sender)) {
            throw new \Exception('Credenciais Twilio não configuradas (api_key, username, sender).');
        }

        // Exemplo de chamada (descomenta quando tiveres conta Twilio):
        //
        // $response = Http::withBasicAuth($this->username, $this->apiKey)
        //     ->asForm()
        //     ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->username}/Messages.json", [
        //         'From' => $this->sender,
        //         'To'   => $sms->telefone,
        //         'Body' => $sms->mensagem,
        //     ]);
        //
        // if ($response->successful()) {
        //     $sms->referencia = $response->json('sid');
        //     $sms->save();
        //     return true;
        // }
        //
        // Log::warning('Falha Twilio', ['resposta' => $response->json()]);
        // return false;

        throw new \Exception('Integração Twilio ainda não implementada. Configura as credenciais e descomenta o código.');
    }

    /**
     * Driver Africa's Talking (https://africastalking.com/)
     *
     * Popular em Moçambique. Requer:
     * - services.sms.api_key   (API Key)
     * - services.sms.username  (Username da conta)
     * - services.sms.sender    (Short code / Sender ID)
     *
     * @param Sms $sms
     * @return bool
     * @throws \Exception
     */
    protected function enviarAfricasTalking(Sms $sms): bool
    {
        if (empty($this->apiKey) || empty($this->username)) {
            throw new \Exception('Credenciais Africa\'s Talking não configuradas (api_key, username).');
        }

        // Exemplo de chamada (descomenta quando tiveres conta):
        //
        // $response = Http::withHeaders([
        //     'apiKey' => $this->apiKey,
        //     'Accept' => 'application/json',
        // ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
        //     'username' => $this->username,
        //     'to'       => $sms->telefone,
        //     'message'  => $sms->mensagem,
        //     'from'     => $this->sender,
        // ]);
        //
        // if ($response->successful()) {
        //     $sms->referencia = $response->json('SMSMessageData.Recipients.0.messageId') ?? null;
        //     $sms->save();
        //     return true;
        // }
        //
        // Log::warning('Falha Africa\'s Talking', ['resposta' => $response->json()]);
        // return false;

        throw new \Exception('Integração Africa\'s Talking ainda não implementada. Configura as credenciais e descomenta o código.');
    }

    // ============================================================
    // MÉTODO AUXILIAR DE DIAGNÓSTICO
    // ============================================================

    /**
     * Testa a configuração atual do driver (sem enviar SMS real)
     *
     * Útil para diagnosticar problemas de configuração.
     *
     * @return array
     */
    public function testarConfiguracao(): array
    {
        return [
            'driver'   => $this->driver,
            'api_key'  => $this->apiKey ? '✓ configurado' : '✗ em falta',
            'api_url'  => $this->apiUrl ?? 'não definido',
            'sender'   => $this->sender ?? 'não definido',
            'username' => $this->username ?? 'não definido',
        ];
    }
}