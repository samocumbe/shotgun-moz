<?php

namespace Database\Seeders;

use App\Models\Cupom;
use App\Models\Evento;
use Illuminate\Database\Seeder;

class CupomSeeder extends Seeder
{
    public function run(): void
    {
        $eventos = Evento::all();

        $cupons = [
            [
                'codigo' => 'PROMO10',
                'nome' => 'Desconto de 10%',
                'tipo' => 'percentual',
                'valor' => 10.00,
                'valor_minimo' => 100.00,
                'quantidade' => 100,
                'validade_inicio' => now(),
                'validade_fim' => now()->addMonths(1),
                'ativo' => true,
            ],
            [
                'codigo' => 'VIP2026',
                'nome' => 'Desconto VIP',
                'tipo' => 'percentual',
                'valor' => 20.00,
                'valor_minimo' => 500.00,
                'quantidade' => 50,
                'validade_inicio' => now(),
                'validade_fim' => now()->addMonths(2),
                'ativo' => true,
            ],
            [
                'codigo' => 'FIXO50',
                'nome' => 'Desconto Fixo de 50 MZN',
                'tipo' => 'fixo',
                'valor' => 50.00,
                'valor_minimo' => 200.00,
                'quantidade' => 200,
                'validade_inicio' => now(),
                'validade_fim' => now()->addMonths(3),
                'ativo' => true,
            ],
        ];

        foreach ($eventos as $evento) {
            foreach ($cupons as $cupom) {
                Cupom::create([
                    'codigo' => $cupom['codigo'] . '-' . $evento->id,
                    'evento_id' => $evento->id,
                    'nome' => $cupom['nome'] . ' - ' . $evento->nome,
                    'tipo' => $cupom['tipo'],
                    'valor' => $cupom['valor'],
                    'valor_minimo' => $cupom['valor_minimo'],
                    'quantidade' => $cupom['quantidade'],
                    'usos' => 0,
                    'validade_inicio' => $cupom['validade_inicio'],
                    'validade_fim' => $cupom['validade_fim'],
                    'ativo' => $cupom['ativo'],
                ]);
            }
        }

        $this->command->info('✅ Cupons criados com sucesso!');
    }
}