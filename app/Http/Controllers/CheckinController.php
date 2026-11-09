<?php

/**
 * ============================================================
 * CONTROLLER: CheckinController
 * ============================================================
 * 
 * Gerencia todas as operações relacionadas a check-ins:
 * - Listagem de check-ins (interface web)
 * - Listagem de check-ins por evento (API)
 * - Registro de check-ins (entrada no evento)
 * - Sincronização de check-ins offline
 * - Listagem de check-ins pendentes
 * - Estatísticas de check-ins
 * - Visualização de detalhes
 * 
 * Suporta validação de ingressos via QR Code,
 * dispositivos móveis (scanners) e sincronização offline.
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Evento;
use App\Models\Ingresso;
use App\Models\Dispositivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class CheckinController extends Controller
{
    /**
     * ============================================================
     * INDEX WEB: Lista check-ins para interface web
     * ============================================================
     * 
     * Esta é a página principal de check-ins, acessada pelo menu lateral.
     * Exibe uma lista completa de todos os check-ins com filtros.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     * 
     * Filtros disponíveis:
     * - evento_id: Filtra por evento específico
     * - status: pendente, sincronizado
     * - data_inicio: Data inicial do filtro
     * - data_fim: Data final do filtro
     * 
     * A view retornada é admin.checkins.index com dados paginados
     * e estatísticas resumidas.
     */
    public function indexWeb(Request $request)
    {
        // Buscar todos os eventos para o filtro (ordenados por nome)
        $eventos = Evento::orderBy('nome')->get();
        
        // ============================================
        // CONSTRUÇÃO DA CONSULTA
        // ============================================
        $query = Checkin::with([
            'evento',           // Dados do evento
            'ingresso.participante', // Dados do participante
            'dispositivo'       // Dados do dispositivo
        ]);
        
        // ============================================
        // APLICAÇÃO DOS FILTROS
        // ============================================
        
        // Filtrar por evento
        if ($request->has('evento_id') && $request->evento_id) {
            $query->where('evento_id', $request->evento_id);
        }
        
        // Filtrar por status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filtrar por data de início
        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('data_checkin', '>=', $request->data_inicio);
        }
        
        // Filtrar por data de fim
        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('data_checkin', '<=', $request->data_fim);
        }
        
        // ============================================
        // EXECUÇÃO DA CONSULTA
        // ============================================
        
        // Ordenar por data de check-in (mais recentes primeiro)
        // Paginar 20 resultados por página
        $checkins = $query->orderBy('data_checkin', 'desc')->paginate(20);
        
        // ============================================
        // ESTATÍSTICAS
        // ============================================
        
        // Total de check-ins no sistema
        $totalCheckins = Checkin::count();
        
        // Check-ins pendentes de sincronização
        $pendentes = Checkin::where('status', Checkin::STATUS_PENDENTE)->count();
        
        // Check-ins já sincronizados
        $sincronizados = Checkin::where('status', Checkin::STATUS_SINCRONIZADO)->count();
        
        // Check-ins realizados hoje
        $checkinsHoje = Checkin::whereDate('data_checkin', today())->count();
        
        // ============================================
        // RETORNO DA VIEW
        // ============================================
        return view('admin.checkins.index', compact(
            'checkins',      // Lista paginada de check-ins
            'eventos',       // Eventos para o filtro
            'totalCheckins', // Total geral
            'pendentes',     // Pendentes
            'sincronizados', // Sincronizados
            'checkinsHoje'   // Check-ins de hoje
        ));
    }

    /**
     * ============================================================
     * INDEX: Lista checkins de um evento específico (API)
     * ============================================================
     * 
     * Esta rota é usada principalmente pela API para listar
     * check-ins de um evento particular.
     * 
     * @param int $eventoId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Filtros disponíveis:
     * - per_page: Quantidade por página (padrão 15)
     * - status: pendente, sincronizado
     */
    public function index($eventoId, Request $request)
    {
        // Verificar se o evento existe
        $evento = Evento::find($eventoId);

        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento não encontrado'
            ], 404);
        }

        // ============================================
        // CONSTRUÇÃO DA CONSULTA
        // ============================================
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        
        $query = Checkin::where('evento_id', $eventoId)
                        ->with(['ingresso', 'ingresso.participante', 'dispositivo']);
        
        // Filtrar por status
        if ($status) {
            $query->where('status', $status);
        }
        
        // ============================================
        // EXECUÇÃO DA CONSULTA
        // ============================================
        $checkins = $query->orderBy('data_checkin', 'desc')
                          ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $checkins,
            'message' => 'Checkins listados com sucesso'
        ]);
    }

    /**
     * ============================================================
     * SHOW: Exibe detalhes de um check-in específico (API)
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * 
     * Retorna todos os dados do check-in com relacionamentos:
     * - Dados do evento
     * - Dados do ingresso
     * - Dados do participante
     * - Dados do dispositivo
     */
    public function show($id)
    {
        try {
            // Buscar o check-in com todos os relacionamentos
            $checkin = Checkin::with([
                'evento', 
                'ingresso.participante', 
                'ingresso.categoria',
                'dispositivo'
            ])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $checkin,
                'message' => 'Detalhes do check-in'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar check-in: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Check-in não encontrado'
            ], 404);
        }
    }

    /**
     * ============================================================
     * STORE: Realiza um checkin (entrada no evento)
     * ============================================================
     * 
     * @param Request $request
     * @param int $eventoId
     * @return \Illuminate\Http\JsonResponse
     * 
     * Fluxo de validação:
     * 1. Verifica se o evento existe
     * 2. Valida os dados recebidos
     * 3. Verifica se o ingresso pertence ao evento
     * 4. Verifica se o ingresso pode ser utilizado
     * 5. Verifica se já existe check-in para este ingresso
     * 6. Cria o check-in
     * 7. Marca o ingresso como utilizado
     * 8. Retorna sucesso com os dados
     */
    public function store(Request $request, $eventoId)
    {
        // ============================================
        // VERIFICAÇÃO DO EVENTO
        // ============================================
        $evento = Evento::find($eventoId);

        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento não encontrado'
            ], 404);
        }

        // ============================================
        // VALIDAÇÃO DOS DADOS
        // ============================================
        $validator = Validator::make($request->all(), [
            'ingresso_id' => 'required|exists:ingressos,id',
            'dispositivo_id' => 'required|exists:dispositivos,id',
            'qr_code' => 'required|string',
            'porta_acesso' => 'nullable|string|max:50',
            'localizacao' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Erro de validação'
            ], 422);
        }

        // ============================================
        // VERIFICAÇÃO DO INGRESSO
        // ============================================
        
        // Verifica se o ingresso pertence ao evento
        $ingresso = Ingresso::where('evento_id', $eventoId)
                            ->find($request->ingresso_id);

        if (!$ingresso) {
            return response()->json([
                'success' => false,
                'message' => 'Ingresso não pertence a este evento'
            ], 422);
        }

        // Verifica se o ingresso pode ser utilizado
        if (!$ingresso->podeSerUtilizado()) {
            return response()->json([
                'success' => false,
                'message' => 'Ingresso não pode ser utilizado no momento'
            ], 422);
        }

        // Verifica se já existe checkin para este ingresso
        if (Checkin::where('ingresso_id', $ingresso->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Checkin já realizado para este ingresso'
            ], 422);
        }

        // ============================================
        // CRIAÇÃO DO CHECK-IN
        // ============================================
        $checkin = Checkin::create([
            'ingresso_id' => $ingresso->id,
            'evento_id' => $eventoId,
            'dispositivo_id' => $request->dispositivo_id,
            'qr_code' => $request->qr_code,
            'data_checkin' => now(),
            'porta_acesso' => $request->porta_acesso,
            'localizacao' => $request->localizacao,
            'status' => Checkin::STATUS_PENDENTE, // Aguardando sincronização
        ]);

        // ============================================
        // ATUALIZAÇÃO DO INGRESSO
        // ============================================
        $ingresso->marcarComoUtilizado();

        // ============================================
        // LOG DA OPERAÇÃO
        // ============================================
        Log::info('Check-in realizado: ' . $checkin->id . ' - Ingresso: ' . $ingresso->id);

        return response()->json([
            'success' => true,
            'data' => $checkin->load(['ingresso', 'ingresso.participante', 'dispositivo']),
            'message' => 'Checkin realizado com sucesso'
        ], 201);
    }

    /**
     * ============================================================
     * SINCRONIZAR: Sincroniza checkins pendentes
     * ============================================================
     * 
     * @param int $eventoId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Esta rota é usada pelo app Android para sincronizar
     * os check-ins realizados offline.
     * 
     * Fluxo:
     * 1. Valida o dispositivo
     * 2. Busca check-ins pendentes do dispositivo
     * 3. Marca cada um como sincronizado
     * 4. Atualiza o último sync do dispositivo
     * 5. Retorna estatísticas da sincronização
     */
    public function sincronizar($eventoId, Request $request)
    {
        // ============================================
        // VALIDAÇÃO
        // ============================================
        $validator = Validator::make($request->all(), [
            'dispositivo_id' => 'required|exists:dispositivos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Erro de validação'
            ], 422);
        }

        // ============================================
        // BUSCA CHECK-INS PENDENTES
        // ============================================
        $checkins = Checkin::where('evento_id', $eventoId)
                           ->where('dispositivo_id', $request->dispositivo_id)
                           ->where('status', Checkin::STATUS_PENDENTE)
                           ->get();

        $sincronizados = 0;
        $falhas = 0;

        // ============================================
        // SINCRONIZAÇÃO
        // ============================================
        foreach ($checkins as $checkin) {
            try {
                // Marcar como sincronizado
                $checkin->marcarComoSincronizado();
                $sincronizados++;
            } catch (\Exception $e) {
                Log::error('Erro ao sincronizar check-in: ' . $e->getMessage());
                $falhas++;
            }
        }

        // ============================================
        // ATUALIZA DISPOSITIVO
        // ============================================
        $dispositivo = Dispositivo::find($request->dispositivo_id);
        $dispositivo->atualizarUltimoSync();

        // ============================================
        // LOG DA OPERAÇÃO
        // ============================================
        Log::info('Sincronização concluída: ' . $sincronizados . ' check-ins sincronizados');

        return response()->json([
            'success' => true,
            'data' => [
                'sincronizados' => $sincronizados,
                'falhas' => $falhas,
                'total' => $checkins->count(),
                'ultimo_sync' => $dispositivo->ultimo_sync,
            ],
            'message' => 'Sincronização concluída'
        ]);
    }

    /**
     * ============================================================
     * PENDENTES: Lista checkins pendentes de um dispositivo
     * ============================================================
     * 
     * @param int $eventoId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Retorna todos os check-ins pendentes de sincronização
     * de um dispositivo específico.
     */
    public function pendentes($eventoId, Request $request)
    {
        // ============================================
        // VALIDAÇÃO
        // ============================================
        $validator = Validator::make($request->all(), [
            'dispositivo_id' => 'required|exists:dispositivos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Erro de validação'
            ], 422);
        }

        // ============================================
        // BUSCA CHECK-INS PENDENTES
        // ============================================
        $checkins = Checkin::where('evento_id', $eventoId)
                           ->where('dispositivo_id', $request->dispositivo_id)
                           ->where('status', Checkin::STATUS_PENDENTE)
                           ->with(['ingresso', 'ingresso.participante'])
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $checkins,
            'count' => $checkins->count(),
            'message' => 'Checkins pendentes listados com sucesso'
        ]);
    }

    /**
     * ============================================================
     * ESTATISTICAS: Estatísticas de checkin do evento
     * ============================================================
     * 
     * @param int $eventoId
     * @return \Illuminate\Http\JsonResponse
     * 
     * Retorna estatísticas detalhadas sobre check-ins:
     * - Total de check-ins
     * - Pendentes vs Sincronizados
     * - Check-ins por porta de acesso
     * - Check-ins por hora do dia
     * - Últimos 10 check-ins realizados
     */
    public function estatisticas($eventoId)
    {
        // ============================================
        // VERIFICAÇÃO DO EVENTO
        // ============================================
        $evento = Evento::find($eventoId);

        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento não encontrado'
            ], 404);
        }

        // ============================================
        // CONSULTAS ESTATÍSTICAS
        // ============================================
        $checkins = Checkin::where('evento_id', $eventoId);
        
        $estatisticas = [
            // Totais
            'total_checkins' => $checkins->count(),
            'pendentes' => $checkins->where('status', Checkin::STATUS_PENDENTE)->count(),
            'sincronizados' => $checkins->where('status', Checkin::STATUS_SINCRONIZADO)->count(),
            
            // Check-ins por porta de acesso
            'checkins_por_porta' => $checkins->selectRaw('porta_acesso, COUNT(*) as total')
                                            ->whereNotNull('porta_acesso')
                                            ->groupBy('porta_acesso')
                                            ->get(),
            
            // Check-ins por hora do dia
            'checkins_por_hora' => $checkins->selectRaw('HOUR(data_checkin) as hora, COUNT(*) as total')
                                           ->groupBy('hora')
                                           ->orderBy('hora')
                                           ->get(),
            
            // Últimos 10 check-ins
            'ultimos_checkins' => $checkins->orderBy('data_checkin', 'desc')
                                          ->limit(10)
                                          ->with(['ingresso.participante'])
                                          ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $estatisticas,
            'message' => 'Estatísticas de checkin'
        ]);
    }
}