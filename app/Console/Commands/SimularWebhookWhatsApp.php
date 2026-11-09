<?php

/**
 * ============================================================
 * COMMAND: Simular Webhook WhatsApp - SHOTGUN-MOZ
 * ============================================================
 *
 * Simula a receção de um webhook da Meta para testes locais,
 * sem precisar de ngrok nem de configurar a Meta.
 *
 * Uso:
 *   php artisan whatsapp:simular-webhook {wamid} {status}
 *
 * Exemplos:
 *   php artisan whatsapp:simular-webhook wamid.TESTE123 delivered
 *   php artisan whatsapp:simular-webhook wamid.TESTE123 read
 *   php artisan whatsapp:simular-webhook wamid.TESTE123 failed
 *
 * Antes de simular, precisas de uma mensagem com `referencia`
 * preenchida. Para testar, podes:
 *
 *   1. Enviar um WhatsApp em massa com driver=log
 *   2. Ir à BD e atualizar a `referencia`:
 *      UPDATE whatsapp_messages SET referencia='wamid.TESTE123'
 *      WHERE id=1;
 *   3. Correr este comando
 * ============================================================
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimularWebhookWhatsApp extends Command
{
    protected $signature = 'whatsapp:simular-webhook
                            {wamid : ID da mensagem (ex: wamid.TESTE123)}
                            {status : sent|delivered|read|failed}
                            {--url=http://localhost:8000 : URL base da app}';

    protected $description = 'Simula um webhook da Meta WhatsApp para testes locais';

    public function handle(): int
    {
        $wamid    = $this->argument('wamid');
        $status   = $this->argument('status');
        $baseUrl  = rtrim($this->option('url'), '/');

        // Validar status
        $statusValidos = ['sent', 'delivered', 'read', 'failed'];
        if (!in_array($status, $statusValidos, true)) {
            $this->error("Status inválido: {$status}");
            $this->line('Válidos: ' . implode(', ', $statusValidos));
            return self::FAILURE;
        }

        // Payload que imita o da Meta
        $statusPayload = [
            'id'           => $wamid,
            'status'       => $status,
            'timestamp'    => (string) time(),
            'recipient_id' => '+258840000000',
        ];

        // Se for falha, adicionar erro
        if ($status === 'failed') {
            $statusPayload['errors'] = [[
                'code'  => 131047,
                'title' => 'Mensagem rejeitada (simulação)',
            ]];
        }

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry'  => [[
                'id'      => 'SIMULADO',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => [
                            'display_phone_number' => '258840000000',
                            'phone_number_id'      => 'SIMULADO',
                        ],
                        'statuses' => [$statusPayload],
                    ],
                ]],
            ]],
        ];

        $url = $baseUrl . '/webhooks/whatsapp';

        $this->info("→ Simulando webhook: {$wamid} → {$status}");
        $this->line("  URL: {$url}");

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->post($url, $payload);

            if ($response->successful()) {
                $this->info("✓ Resposta: {$response->status()} - {$response->body()}");
                return self::SUCCESS;
            }

            $this->error("✗ Falha: {$response->status()}");
            $this->line($response->body());
            return self::FAILURE;

        } catch (\Throwable $e) {
            $this->error("✗ Erro: {$e->getMessage()}");
            $this->line('  Verifica se o servidor está a correr (php artisan serve).');
            return self::FAILURE;
        }
    }
}