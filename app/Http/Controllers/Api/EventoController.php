<?php

/**
 * ============================================================
 * CONTROLLER: EventoController (API) - SHOTGUN-MOZ
 * ============================================================
 *
 * Lista e detalha eventos para a app mobile.
 *
 * Endpoints:
 * - GET /api/v1/eventos       → lista eventos publicados
 * - GET /api/v1/eventos/{id}  → detalhes + categorias
 *
 * ⚠️ REGRA: Apenas eventos que ainda NÃO ACABARAM.
 *    Se tiver data_fim, usa-a. Se não, usa data_inicio.
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    /**
     * GET /api/v1/eventos
     * Lista eventos futuros (publicados ou encerrados), com filtros opcionais.
     */
    public function index(Request $request)
    {
        $query = Evento::with(['local', 'organizacao', 'categoriasIngressos'])
            ->whereIn('status', ['publicado', 'encerrado'])
            // ⬇️ REGRA NOVA: Só eventos que ainda não acabaram ⬇️
            ->where(function ($q) {
                $q->where(function ($q2) {
                    // Se tem data_fim, verifica se ainda não chegou
                    $q2->whereNotNull('data_fim')
                       ->where('data_fim', '>=', now());
                })->orWhere(function ($q2) {
                    // Se não tem data_fim, verifica data_inicio
                    $q2->whereNull('data_fim')
                       ->where('data_inicio', '>=', now());
                });
            });
            // ⬆️ ⬆️ ⬆️

        // Filtro por nome (busca)
        if ($request->filled('busca')) {
            $query->where('nome', 'like', '%' . $request->busca . '%');
        }

        // Filtro por local
        if ($request->filled('local_id')) {
            $query->where('local_id', $request->local_id);
        }

        // Filtro por data (a partir de)
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_inicio', '>=', $request->data_inicio);
        }

        // Ordenação
        $ordenar = $request->input('ordenar', 'data_inicio_asc');
        switch ($ordenar) {
            case 'data_inicio_desc':
                $query->orderBy('data_inicio', 'desc');
                break;
            case 'nome_asc':
                $query->orderBy('nome', 'asc');
                break;
            default:
                $query->orderBy('data_inicio', 'asc');
        }

        $eventos = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $eventos->items(),
            'meta'    => [
                'current_page' => $eventos->currentPage(),
                'last_page'    => $eventos->lastPage(),
                'per_page'     => $eventos->perPage(),
                'total'        => $eventos->total(),
            ],
            'message' => 'Eventos listados com sucesso',
        ]);
    }

    /**
     * GET /api/v1/eventos/{id}
     * Detalhes de um evento específico.
     */
    public function show($id)
    {
        $evento = Evento::with(['local', 'organizacao', 'categoriasIngressos'])
            ->find($id);

        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento não encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $evento,
            'message' => 'Evento encontrado com sucesso',
        ]);
    }
}