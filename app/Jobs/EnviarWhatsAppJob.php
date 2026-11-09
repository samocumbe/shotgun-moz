<?php

/**
 * ============================================================
 * JOB: EnviarWhatsAppJob - SHOTGUN-MOZ
 * ============================================================
 *
 * Job assíncrono que envia uma mensagem WhatsApp via
 * WhatsAppService.
 *
 * É colocado na fila pelo WhatsAppMassaController::enviar().
 *
 * Notas:
 * - O WhatsAppService já grava o registo na tabela
 *   whatsapp_messages e devolve ['success'=>, 'message'=>, 'id'=>].
 * - Se o serviço devolver 'success=false' MAS já tiver criado
 *   o registo (id presente), NÃO re-tentamos — o registo já está
 *   marcado como falha e não queremos duplicar linhas.
 * - Se o serviço lançar exceção antes de gravar, aí sim lançamos
 *   para a fila re-tentar (com backoff).
 */

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Nº de tentativas em caso de falha */
    public int $tries = 3;

    /** Timeout do job (segundos) */
    public int $timeout = 60;

    /** Backoff entre tentativas (segundos) */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $telefone,
        public string $mensagem,
        public array $dados = []
    ) {}

    /**
     * Executa o job.
     * O Laravel injeta automaticamente o WhatsAppService.
     */
    public function handle(WhatsAppService $whatsappService): void
    {
        try {
            $resultado = $whatsappService->enviarWhatsApp(
                $this->telefone,
                $this->mensagem,
                $this->dados
            );

            // ----- Envio com falha -----
            if (!$resultado['success']) {
                Log::warning('Falha ao enviar WhatsApp em massa', [
                    'telefone' => $this->telefone,
                    'erro'     => $resultado['message'] ?? 'Erro desconhecido',
                    'lote_id'  => $this->dados['lote_id'] ?? null,
                ]);

                // Se o serviço criou registo (id presente), a mensagem já
                // está marcada como falha na BD → NÃO re-tentar.
                if (!isset($resultado['id'])) {
                    throw new \Exception($resultado['message'] ?? 'Erro no envio');
                }
                return;
            }

            // ----- Envio OK -----
            Log::info('WhatsApp enviado em massa', [
                'telefone'    => $this->telefone,
                'whatsapp_id' => $resultado['id'] ?? null,
                'lote_id'     => $this->dados['lote_id'] ?? null,
            ]);

        } catch (\Throwable $e) {
            Log::error('Exceção no EnviarWhatsAppJob', [
                'telefone' => $this->telefone,
                'erro'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /** Nome amigável para logs e Horizon */
    public function displayName(): string
    {
        return "EnviarWhatsAppJob: {$this->telefone}";
    }
}