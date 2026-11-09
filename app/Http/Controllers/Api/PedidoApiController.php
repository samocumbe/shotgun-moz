<?php

/**
 * ============================================================
 * CONTROLLER: PedidoApiController - SHOTGUN-MOZ
 * ============================================================
 *
 * Gere a compra de ingressos pela app mobile.
 *
 * Endpoints:
 * - POST /api/v1/pedidos/checkout → cria pedido + gera ingressos + simula pagamento
 * - GET  /api/v1/pedidos          → lista pedidos do user
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Ingresso;
use App\Models\Evento;
use App\Models\CategoriaIngresso;
use App\Models\CarteiraIngresso;
use App\Models\Participante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PedidoApiController extends Controller
{
    /**
     * POST /api/v1/pedidos/checkout
     *
     * Cria um pedido, gera os ingressos e coloca-os na carteira.
     * Simula o pagamento como aprovado imediatamente.
     */
    public function checkout(Request $request)
    {
        // 1. Validação básica
        $validator = Validator::make($request->all(), [
            'evento_id'          => 'required|exists:eventos,id',
            'categoria_id'       => 'required|exists:categorias_ingressos,id',
            'quantidade'         => 'required|integer|min:1|max:10',
            'metodo_pagamento'   => 'required|string|in:mpesa,emola',
            'telefone_pagamento' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Erro de validação',
            ], 422);
        }

        $user = $request->user();
        $evento = Evento::findOrFail($request->evento_id);
        $categoria = CategoriaIngresso::findOrFail($request->categoria_id);

        // 2. Verificar se a categoria pertence ao evento
        if ($categoria->evento_id !== $evento->id) {
            return response()->json([
                'success' => false,
                'message' => 'Esta categoria de ingresso não pertence ao evento selecionado.',
            ], 400);
        }

        // 3. Verificar stock real (quantidade_total - quantidade_vendida)
        $quantidade = (int) $request->quantidade;
        $disponivel = $categoria->quantidade_total - $categoria->quantidade_vendida;

        if ($disponivel < $quantidade) {
            return response()->json([
                'success' => false,
                'message' => "Stock insuficiente. Apenas restam {$disponivel} ingressos.",
            ], 400);
        }

        // 4. Garantir que o utilizador tem um participante associado
        $participante = Participante::firstOrCreate(
            ['email' => $user->email],
            [
                'uuid'          => (string) Str::uuid(),
                'nome_completo' => $user->name,
                'telefone'      => $user->telefone ?? $request->telefone_pagamento,
                'user_id'       => $user->id,
            ]
        );

        // 5. Iniciar transação
        DB::beginTransaction();
        try {
            // 5.1. Criar Pedido
            $total = $categoria->preco * $quantidade;
            
            $pedido = Pedido::create([
                'evento_id'       => $evento->id,
                'participante_id' => $participante->id,
                'user_id'         => $user->id,
                'subtotal'        => $total,
                'desconto'        => 0,
                'total'           => $total,
                'status'          => 'pago', // ⚠️ SIMULAÇÃO: assumimos pagamento imediato
                'tipo_venda'      => 'online',
                'data_pagamento'  => now(),
                'observacoes'     => "Pago via {$request->metodo_pagamento} - Tel: {$request->telefone_pagamento}",
            ]);

            // 5.2. Gerar os Ingressos
            for ($i = 0; $i < $quantidade; $i++) {
                $ingresso = Ingresso::create([
                    'evento_id'             => $evento->id,
                    'categoria_ingresso_id' => $categoria->id,
                    'participante_id'       => $participante->id,
                    'preco_pago'            => $categoria->preco,
                    'status'                => 'pago',
                    'data_compra'           => now(),
                ]);

                // 5.3. Colocar na Carteira do utilizador
                CarteiraIngresso::create([
                    'user_id'       => $user->id,
                    'ingresso_id'   => $ingresso->id,
                    'codigo_acesso' => 'ACC-' . strtoupper(Str::random(10)),
                    'status'        => 'ativo',
                    'data_ativacao' => now(),
                ]);
            }

            // 5.4. Atualizar o stock vendido na categoria
            $categoria->increment('quantidade_vendida', $quantidade);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compra efetuada com sucesso!',
                'data'    => [
                    'pedido_id'     => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido,
                    'total'         => $pedido->total,
                    'quantidade'    => $quantidade,
                ],
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar a compra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/pedidos
     * Lista os pedidos do utilizador autenticado.
     */
    public function index(Request $request)
    {
        $pedidos = Pedido::with(['evento'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $pedidos,
            'message' => 'Pedidos listados com sucesso',
        ]);
    }
}