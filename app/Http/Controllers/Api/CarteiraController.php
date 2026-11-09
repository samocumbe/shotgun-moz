<?php

/**
 * ============================================================
 * CONTROLLER: CarteiraController (API) - SHOTGUN-MOZ
 * ============================================================
 *
 * Carteira digital do cliente (saldo + ingressos).
 *
 * Endpoints:
 * - GET /api/v1/carteira          → resumo geral
 * - GET /api/v1/carteira/ingressos → lista ingressos
 * - GET /api/v1/carteira/saldo     → só o saldo
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarteiraIngresso;
use Illuminate\Http\Request;

class CarteiraController extends Controller
{
    /**
     * GET /api/v1/carteira
     * Resumo geral da carteira do utilizador autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $ingressos = CarteiraIngresso::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'user_id'           => $user->id,
                'saldo_mzn'         => 0,   // ajustar quando existir tabela de saldo
                'total_ingressos'   => $ingressos->count(),
                'ingressos_validos' => $ingressos->where('status', 'valido')->count(),
                'ingressos_usados'  => $ingressos->where('status', 'utilizado')->count(),
            ],
            'message' => 'Carteira carregada com sucesso',
        ]);
    }

    /**
     * GET /api/v1/carteira/ingressos
     */
    public function ingressos(Request $request)
    {
        $user = $request->user();

        $ingressos = CarteiraIngresso::with(['ingresso.evento', 'ingresso.categoria'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'   => true,
            'total'     => $ingressos->count(),
            'ingressos' => $ingressos,
            'message'   => 'Ingressos carregados com sucesso',
        ]);
    }

    /**
     * GET /api/v1/carteira/saldo
     */
    public function saldo(Request $request)
    {
        return response()->json([
            'success'   => true,
            'saldo_mzn' => 0,   // ajustar quando existir tabela de saldo
            'moeda'     => 'MZN',
            'message'   => 'Saldo obtido com sucesso',
        ]);
    }
}