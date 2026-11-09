<?php

namespace App\Jobs;

use App\Models\Sms;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de tentativas em caso de falha
     */
    public int $tries = 3;

    /**
     * Timeout do job (em segundos)
     */
    public int $timeout = 60;

    /**
     * Backoff entre tentativas (segundos)
     */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $telefone,
        public string $mensagem,
        public array $dados = []
    ) {}

    /**
     * Executa o job
     */
    public function handle(SmsService $smsService): void
    {
        try {
            $resultado = $smsService->enviarSms(
                $this->telefone,
                $this->mensagem,
                $this->dados
            );

            if (!$resultado['success']) {
                Log::warning('Falha ao enviar SMS em massa', [
                    'telefone' => $this->telefone,
                    'erro'     => $resultado['message'] ?? 'Erro desconhecido',
                ]);

                // Lançar exceção para re-tentar
                throw new \Exception($resultado['message'] ?? 'Erro no envio');
            }

            Log::info('SMS enviado em massa', [
                'telefone' => $this->telefone,
                'sms_id'   => $resultado['id'] ?? null,
            ]);

        } catch (\Throwable $e) {
            Log::error('Exceção no EnviarSmsJob', [
                'telefone' => $this->telefone,
                'erro'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Nome do job para logs
     */
    public function displayName(): string
    {
        return "EnviarSmsJob: {$this->telefone}";
    }
}