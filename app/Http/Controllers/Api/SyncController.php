<?php

/**
 * ============================================================
 * CONTROLLER: SyncController (API) - SHOTGUN-MOZ
 * ============================================================
 *
 * Sincronização offline de check-ins.
 *
 * Endpoints:
 * - POST /api/v1/sync/checkins  → recebe lote de check-ins
 * - GET  /api/v1/sync/pendentes → lista pendências
 * - POST /api/v1/sync/confirmar → confirma sincronização
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\Dispositivo;
use App\Models\SyncQueue;
use App\Models\Ingresso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    /**
     * POST /api/v1/sync/checkins
     *
     * Recebe um lote de check-ins feitos offline.
     */
    public function syncCheckins(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dispositivo_uuid'         => 'required|exists:dispositivos,uuid',
            'checkins'                 => 'required|array|min:1',
            'checkins.*.uuid'          => 'required|string',
            'checkins.*.qr_code'       => 'required|string',
            'checkins.*.ingresso_id'   => 'required|exists:ingressos,id',
            'checkins.*.evento_id'     => 'required|exists:eventos,id',
            'checkins.*.data_checkin'  => 'required|date',
            'checkins.*.porta_acesso'  => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Erro de validação',
            ], 422);
        }

        $dispositivo = Dispositivo::where('uuid', $request->dispositivo_uuid)->first();

        $sincronizados = 0;
        $falhas = 0;
        $resultados = [];

        foreach ($request->checkins as $checkinData) {
            DB::beginTransaction();
            try {
                // Já existe? Só marca como sincronizado
                $existing = Checkin::where('uuid', $checkinData['uuid'])->first();
                if ($existing) {
                    $existing->status = 'sincronizado';
                    $existing->save();
                    DB::commit();
                    $sincronizados++;
                    $resultados[] = [
                        'uuid'    => $checkinData['uuid'],
                        'status'  => 'already_synced',
                        'message' => 'Checkin já sincronizado',
                    ];
                    continue;
                }

                $ingresso = Ingresso::find($checkinData['ingresso_id']);
                if ($ingresso && $ingresso->isUtilizado()) {
                    DB::rollBack();
                    $falhas++;
                    $resultados[] = [
                        'uuid'    => $checkinData['uuid'],
                        'status'  => 'error',
                        'message' => 'Ingresso já utilizado',
                    ];
                    continue;
                }

                $checkin = Checkin::create([
                    'uuid'           => $checkinData['uuid'],
                    'ingresso_id'    => $checkinData['ingresso_id'],
                    'evento_id'      => $checkinData['evento_id'],
                    'dispositivo_id' => $dispositivo->id,
                    'qr_code'        => $checkinData['qr_code'],
                    'data_checkin'   => $checkinData['data_checkin'],
                    'porta_acesso'   => $checkinData['porta_acesso'] ?? null,
                    'status'         => 'sincronizado',
                ]);

                if ($ingresso) {
                    $ingresso->marcarComoUtilizado();
                }

                DB::commit();
                $sincronizados++;
                $resultados[] = [
                    'uuid'    => $checkinData['uuid'],
                    'id'      => $checkin->id,
                    'status'  => 'synced',
                    'message' => 'Checkin sincronizado com sucesso',
                ];

            } catch (\Throwable $e) {
                DB::rollBack();
                $falhas++;
                $resultados[] = [
                    'uuid'    => $checkinData['uuid'],
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ];
                Log::error('Erro no sync de check-in', [
                    'uuid' => $checkinData['uuid'],
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        $dispositivo->atualizarUltimoSync();

        // Registar na fila de sincronização
        SyncQueue::create([
            'uuid'           => (string) Str::uuid(),
            'dispositivo_id' => $dispositivo->id,
            'tipo'           => 'checkin',
            'entidade'       => 'checkins',
            'entidade_id'    => $dispositivo->id,
            'payload'        => [
                'sincronizados' => $sincronizados,
                'falhas'        => $falhas,
                'resultados'    => $resultados,
            ],
            'status'    => 'sucesso',
            'synced_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'sincronizados' => $sincronizados,
                'falhas'        => $falhas,
                'total'         => count($request->checkins),
                'resultados'    => $resultados,
                'ultimo_sync'   => $dispositivo->ultimo_sync,
            ],
            'message' => 'Sincronização concluída',
        ]);
    }

    /**
     * GET /api/v1/sync/pendentes
     */
    public function pendentes(Request $request)
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

        $dispositivo = Dispositivo::where('uuid', $request->dispositivo_uuid)->first();

        $pendentes = Checkin::where('dispositivo_id', $dispositivo->id)
            ->where('status', 'pendente')
            ->with(['ingresso', 'ingresso.participante', 'evento'])
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'pendentes' => $pendentes->map(function ($checkin) {
                    return [
                        'uuid'         => $checkin->uuid,
                        'qr_code'      => $checkin->qr_code,
                        'ingresso_id'  => $checkin->ingresso_id,
                        'evento_id'    => $checkin->evento_id,
                        'data_checkin' => $checkin->data_checkin,
                        'porta_acesso' => $checkin->porta_acesso,
                        'participante' => [
                            'nome'  => $checkin->ingresso->participante->nome_completo ?? 'N/A',
                            'email' => $checkin->ingresso->participante->email ?? 'N/A',
                        ],
                        'evento' => [
                            'nome' => $checkin->evento->nome ?? 'N/A',
                        ],
                    ];
                }),
                'total'       => $pendentes->count(),
                'ultimo_sync' => $dispositivo->ultimo_sync,
            ],
            'message' => 'Pendências listadas com sucesso',
        ]);
    }

    /**
     * POST /api/v1/sync/confirmar
     */
    public function confirmar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid'             => 'required|string|exists:checkins,uuid',
            'dispositivo_uuid' => 'required|exists:dispositivos,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Erro de validação',
            ], 422);
        }

        $checkin = Checkin::where('uuid', $request->uuid)->first();
        $checkin->status = 'sincronizado';
        $checkin->save();

        return response()->json([
            'success' => true,
            'data'    => [
                'uuid'   => $checkin->uuid,
                'status' => $checkin->status,
            ],
            'message' => 'Checkin confirmado com sucesso',
        ]);
    }
}