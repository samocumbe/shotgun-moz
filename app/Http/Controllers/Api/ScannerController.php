<?php

/**
 * ============================================================
 * CONTROLLER: ScannerController (API) - SHOTGUN-MOZ
 * ============================================================
 *
 * Validação de QR codes de ingressos (check-in).
 *
 * Endpoints:
 * - POST /api/v1/scanner/validar                     → valida QR
 * - GET  /api/v1/scanner/eventos/{eventoId}/ingressos → lista p/ sync
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingresso;
use App\Models\Evento;
use App\Models\Checkin;
use App\Models\Dispositivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ScannerController extends Controller
{
    /**
     * POST /api/v1/scanner/validar
     *
     * Body:
     * {
     *   "qr_code": "EVT2026.A8D92K.7F72A9",
     *   "evento_id": 1,
     *   "dispositivo_uuid": "abc-123",
     *   "porta_acesso": "Principal"
     * }
     */
    public function validar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code'          => 'required|string',
            'evento_id'        => 'required|exists:eventos,id',
            'dispositivo_uuid' => 'required|exists:dispositivos,uuid',
            'porta_acesso'     => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Erro de validação',
            ], 422);
        }

        $dispositivo = Dispositivo::where('uuid', $request->dispositivo_uuid)->first();

        $ingresso = Ingresso::where('evento_id', $request->evento_id)
            ->where('qr_code', $request->qr_code)
            ->with(['evento', 'categoria', 'participante'])
            ->first();

        if (!$ingresso) {
            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => 'Ingresso inválido',
                'reason'  => 'QR Code não encontrado',
            ], 404);
        }

        // Verificar se pode ser utilizado
        if (!$ingresso->podeSerUtilizado()) {
            $motivo = 'Ingresso inválido';
            if ($ingresso->isCancelado()) {
                $motivo = 'Ingresso cancelado';
            } elseif ($ingresso->isUtilizado()) {
                $motivo = 'Ingresso já utilizado';
            } elseif (!$ingresso->isPago()) {
                $motivo = 'Ingresso não pago';
            }

            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => $motivo,
                'data'    => [
                    'status'   => $ingresso->status,
                    'ingresso' => [
                        'id'      => $ingresso->id,
                        'qr_code' => $ingresso->qr_code,
                        'status'  => $ingresso->status,
                    ],
                ],
            ], 422);
        }

        if ($ingresso->checkin()->exists()) {
            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => 'Ingresso já utilizado',
                'data'    => [
                    'checkin'  => $ingresso->checkin,
                    'ingresso' => [
                        'id'      => $ingresso->id,
                        'qr_code' => $ingresso->qr_code,
                    ],
                ],
            ], 422);
        }

        // Transação atómica
        DB::beginTransaction();
        try {
            $ingresso->marcarComoUtilizado();

            $checkin = Checkin::create([
                'uuid'           => (string) Str::uuid(),
                'ingresso_id'    => $ingresso->id,
                'evento_id'      => $request->evento_id,
                'dispositivo_id' => $dispositivo->id,
                'qr_code'        => $ingresso->qr_code,
                'data_checkin'   => now(),
                'porta_acesso'   => $request->porta_acesso,
                'status'         => 'pendente',
            ]);

            $dispositivo->atualizarUltimoSync();

            DB::commit();

            Log::info('Check-in via API', [
                'checkin_id'  => $checkin->id,
                'ingresso_id' => $ingresso->id,
                'user_id'     => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'valid'   => true,
                'data'    => [
                    'checkin' => [
                        'id'          => $checkin->id,
                        'uuid'        => $checkin->uuid,
                        'data_checkin'=> $checkin->data_checkin,
                        'status'      => $checkin->status,
                    ],
                    'ingresso' => [
                        'id'      => $ingresso->id,
                        'qr_code' => $ingresso->qr_code,
                        'status'  => $ingresso->status,
                    ],
                    'participante' => [
                        'id'    => $ingresso->participante->id,
                        'nome'  => $ingresso->participante->nome_completo,
                        'email' => $ingresso->participante->email,
                    ],
                    'evento' => [
                        'id'   => $ingresso->evento->id,
                        'nome' => $ingresso->evento->nome,
                    ],
                ],
                'message' => 'Entrada liberada!',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro no check-in via API', [
                'erro' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao registar check-in: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/scanner/eventos/{eventoId}/ingressos
     *
     * Lista ingressos válidos de um evento para download offline.
     */
    public function ingressos(Request $request, $eventoId)
    {
        $validator = Validator::make($request->all(), [
            'dispositivo_uuid' => 'required|exists:dispositivos,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Erro de validação',
            ], 422);
        }

        $evento = Evento::find($eventoId);
        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento não encontrado',
            ], 404);
        }

        $ingressos = Ingresso::where('evento_id', $eventoId)
            ->where('status', 'pago')
            ->whereNull('data_utilizacao')
            ->with(['participante', 'categoria'])
            ->get();

        $dispositivo = Dispositivo::where('uuid', $request->dispositivo_uuid)->first();
        $dispositivo->atualizarUltimoSync();

        return response()->json([
            'success' => true,
            'data'    => [
                'evento' => [
                    'id'                 => $evento->id,
                    'nome'               => $evento->nome,
                    'capacidade_total'   => $evento->capacidade_total,
                    'ingressos_vendidos' => $evento->ingressos_vendidos,
                ],
                'ingressos' => $ingressos->map(function ($ingresso) {
                    return [
                        'id'       => $ingresso->id,
                        'qr_code'  => $ingresso->qr_code,
                        'participante' => [
                            'nome'  => $ingresso->participante->nome_completo ?? 'N/A',
                            'email' => $ingresso->participante->email ?? 'N/A',
                        ],
                        'categoria' => [
                            'nome'  => $ingresso->categoria->nome ?? 'N/A',
                            'preco' => $ingresso->categoria->preco ?? 0,
                        ],
                        'status' => $ingresso->status,
                    ];
                }),
                'total'       => $ingressos->count(),
                'ultimo_sync' => $dispositivo->ultimo_sync,
            ],
            'message' => 'Ingressos carregados com sucesso',
        ]);
    }
}