<?php

namespace Database\Seeders;

use App\Models\Evento;  // ← Nome do modelo em português
use App\Models\Local;   // ← Nome do modelo em português
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventoSeeder extends Seeder
{
    public function run(): void
    {
        $eventos = [
            [
                'nome' => 'Summer Festival Maputo 2026',
                'descricao' => 'O maior festival de música do verão em Maputo! 3 dias de música, cultura e diversão.',
                'data_inicio' => '2026-12-15',
                'data_fim' => '2026-12-17',
                'hora_inicio' => '18:00:00',
                'hora_fim' => '00:00:00',
                'capacidade_total' => 5000,
                'status' => 'publicado',
            ],
            [
                'nome' => 'Tech Summit 2026',
                'descricao' => 'O maior evento de tecnologia e inovação em Moçambique.',
                'data_inicio' => '2026-10-20',
                'data_fim' => '2026-10-21',
                'hora_inicio' => '09:00:00',
                'hora_fim' => '18:00:00',
                'capacidade_total' => 2000,
                'status' => 'publicado',
            ],
            [
                'nome' => 'Beira Jazz Night',
                'descricao' => 'Uma noite de jazz com os melhores músicos do país.',
                'data_inicio' => '2026-11-05',
                'data_fim' => '2026-11-05',
                'hora_inicio' => '20:00:00',
                'hora_fim' => '23:59:00',
                'capacidade_total' => 800,
                'status' => 'publicado',
            ],
            [
                'nome' => 'Nampula Fashion Week',
                'descricao' => 'A semana de moda mais esperada do norte de Moçambique.',
                'data_inicio' => '2026-09-10',
                'data_fim' => '2026-09-12',
                'hora_inicio' => '15:00:00',
                'hora_fim' => '22:00:00',
                'capacidade_total' => 1500,
                'status' => 'rascunho',
            ],
            [
                'nome' => 'Festival Cultural Moçambique',
                'descricao' => 'Celebrando a diversidade cultural de Moçambique.',
                'data_inicio' => '2026-07-25',
                'data_fim' => '2026-07-27',
                'hora_inicio' => '10:00:00',
                'hora_fim' => '20:00:00',
                'capacidade_total' => 3000,
                'status' => 'publicado',
            ],
        ];

        // Buscar locais existentes
        $locais = Local::all();

        if ($locais->isEmpty()) {
            $this->command->error('Nenhum local encontrado! Execute LocalSeeder primeiro.');
            return;
        }

        foreach ($eventos as $index => $evento) {
            $local = $locais[$index % $locais->count()];
            
            Evento::create([
                'uuid' => (string) Str::uuid(),
                'local_id' => $local->id,
                'nome' => $evento['nome'],
                'slug' => Str::slug($evento['nome'] . '-' . uniqid()),
                'descricao' => $evento['descricao'],
                'data_inicio' => $evento['data_inicio'],
                'data_fim' => $evento['data_fim'],
                'hora_inicio' => $evento['hora_inicio'],
                'hora_fim' => $evento['hora_fim'],
                'capacidade_total' => $evento['capacidade_total'],
                'ingressos_vendidos' => 0,
                'status' => $evento['status'],
            ]);
        }
        
        $this->command->info('✅ Eventos criados: ' . count($eventos));
    }
}