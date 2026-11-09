<?php

namespace Database\Seeders;

use App\Models\CategoriaIngresso;  // ← Model correto
use App\Models\Evento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaIngressoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nome' => 'Normal',
                'descricao' => 'Ingresso padrão para acesso ao evento',
                'preco' => 500.00,
                'quantidade_total' => 2000,
                'tipo' => 'normal',
            ],
            [
                'nome' => 'VIP',
                'descricao' => 'Acesso VIP com benefícios exclusivos',
                'preco' => 1500.00,
                'quantidade_total' => 500,
                'tipo' => 'vip',
            ],
            [
                'nome' => 'Backstage',
                'descricao' => 'Acesso aos bastidores e área exclusiva',
                'preco' => 2500.00,
                'quantidade_total' => 100,
                'tipo' => 'backstage',
            ],
        ];

        $eventos = Evento::where('status', 'publicado')->get();

        foreach ($eventos as $evento) {
            // Cada evento recebe 3 categorias
            foreach ($categorias as $categoria) {
                // Calcular quantidade proporcional à capacidade do evento
                $quantidade = min(
                    $categoria['quantidade_total'],
                    floor($evento->capacidade_total / 3)
                );
                
                CategoriaIngresso::create([
                    'evento_id' => $evento->id,
                    'nome' => $categoria['nome'],
                    'slug' => Str::slug($categoria['nome'] . '-' . uniqid()),
                    'descricao' => $categoria['descricao'],
                    'preco' => $categoria['preco'],
                    'quantidade_total' => $quantidade,
                    'quantidade_vendida' => 0,
                    'tipo' => $categoria['tipo'],
                ]);
            }
        }
        
        $this->command->info('✅ Categorias de ingressos criadas!');
    }
}