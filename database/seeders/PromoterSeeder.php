<?php

namespace Database\Seeders;

use App\Models\Promoter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromoterSeeder extends Seeder
{
    public function run(): void
    {
        $promoters = [
            [
                'nome' => 'João da Silva',
                'email' => 'joao@promoter.com',
                'telefone' => '821234567',
                'nuit' => '123456789',
                'comissao_padrao' => 15.00,
                'status' => 'ativo',
            ],
            [
                'nome' => 'Maria Santos',
                'email' => 'maria@promoter.com',
                'telefone' => '827654321',
                'nuit' => '987654321',
                'comissao_padrao' => 10.00,
                'status' => 'ativo',
            ],
            [
                'nome' => 'Carlos Pereira',
                'email' => 'carlos@promoter.com',
                'telefone' => '823456789',
                'nuit' => '456789123',
                'comissao_padrao' => 20.00,
                'status' => 'pendente',
            ],
            [
                'nome' => 'Ana Rodrigues',
                'email' => 'ana@promoter.com',
                'telefone' => '829876543',
                'nuit' => '789123456',
                'comissao_padrao' => 12.00,
                'status' => 'ativo',
            ],
            [
                'nome' => 'Paulo Mendes',
                'email' => 'paulo@promoter.com',
                'telefone' => '825678912',
                'nuit' => '321654987',
                'comissao_padrao' => 18.00,
                'status' => 'inativo',
            ],
        ];

        foreach ($promoters as $promoter) {
            Promoter::create([
                'uuid' => (string) Str::uuid(),
                'nome' => $promoter['nome'],
                'email' => $promoter['email'],
                'telefone' => $promoter['telefone'],
                'nuit' => $promoter['nuit'],
                'codigo_promocional' => 'PROMO-' . strtoupper(Str::random(6)),
                'link_personalizado' => 'promoter/' . Str::slug($promoter['nome']) . '-' . Str::random(6),
                'comissao_padrao' => $promoter['comissao_padrao'],
                'status' => $promoter['status'],
                'total_vendas' => 0,
                'total_comissao' => 0,
            ]);
        }

        $this->command->info('✅ Promoters criados: ' . count($promoters));
    }
}