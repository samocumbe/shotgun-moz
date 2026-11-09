<?php

namespace Database\Seeders;

use App\Models\Venda;
use App\Models\Evento;
use App\Models\User;
use App\Models\Participante;
use App\Models\Pedido;
use App\Models\ItemPedido;
use App\Models\Ingresso;
use App\Models\CategoriaIngresso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VendaSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar se há usuários
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->error('❌ Nenhum usuário encontrado!');
            $this->command->info('📌 Crie um usuário primeiro: php artisan tinker');
            return;
        }

        $eventos = Evento::where('status', 'publicado')->get();

        // Criar participantes se não existirem
        if (Participante::count() === 0) {
            $this->command->info('📌 Criando participantes...');
            for ($i = 1; $i <= 10; $i++) {
                Participante::create([
                    'uuid' => (string) Str::uuid(),
                    'nome_completo' => "Participante {$i}",
                    'email' => "participante{$i}@email.com",
                    'telefone' => "82{$i}123456",
                ]);
            }
            $this->command->info('✅ Participantes criados: 10');
        }

        $participantes = Participante::all();

        if ($participantes->isEmpty()) {
            $this->command->error('❌ Nenhum participante encontrado!');
            return;
        }

        foreach ($eventos as $evento) {
            $categorias = $evento->categoriasIngressos;
            
            if ($categorias->isEmpty()) {
                continue;
            }

            // Criar 3 vendas para cada evento
            for ($i = 0; $i < 3; $i++) {
                $participante = $participantes->random();
                $categoria = $categorias->random();
                $quantidade = rand(1, 3);
                $user = $users->random();
                
                $pedido = Pedido::create([
                    'uuid' => (string) Str::uuid(),
                    'evento_id' => $evento->id,
                    'participante_id' => $participante->id,
                    'user_id' => $user->id,
                    'numero_pedido' => 'PED-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                    'subtotal' => $categoria->preco * $quantidade,
                    'desconto' => 0,
                    'total' => $categoria->preco * $quantidade,
                    'status' => 'pago',
                    'tipo_venda' => ['online', 'presencial', 'promoter'][rand(0, 2)],
                    'data_pagamento' => now()->subDays(rand(0, 10)),
                ]);

                for ($j = 0; $j < $quantidade; $j++) {
                    $ingresso = Ingresso::create([
                        'uuid' => (string) Str::uuid(),
                        'qr_code' => 'EVT' . date('Y') . '.' . strtoupper(Str::random(6)) . '.' . strtoupper(Str::random(8)),
                        'evento_id' => $evento->id,
                        'categoria_ingresso_id' => $categoria->id,
                        'participante_id' => $participante->id,
                        'preco_pago' => $categoria->preco,
                        'status' => 'pago',
                        'data_compra' => now(),
                    ]);

                    $categoria->increment('quantidade_vendida');
                    $evento->increment('ingressos_vendidos');

                    ItemPedido::create([
                        'pedido_id' => $pedido->id,
                        'categoria_ingresso_id' => $categoria->id,
                        'ingresso_id' => $ingresso->id,
                        'quantidade' => 1,
                        'preco_unitario' => $categoria->preco,
                        'subtotal' => $categoria->preco,
                    ]);
                }

                Venda::create([
                    'uuid' => (string) Str::uuid(),
                    'evento_id' => $evento->id,
                    'user_id' => $user->id,
                    'pedido_id' => $pedido->id,
                    'participante_id' => $participante->id,
                    'tipo' => ['presencial', 'online', 'promoter'][rand(0, 2)],
                    'valor_total' => $categoria->preco * $quantidade,
                    'status' => 'confirmada',
                    'data_venda' => now()->subDays(rand(0, 10)),
                ]);
            }
        }

        $this->command->info('✅ Vendas criadas com sucesso!');
    }
}