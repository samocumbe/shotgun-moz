<?php

namespace Database\Seeders;

use App\Models\Local;
use Illuminate\Database\Seeder;

class LocalSeeder extends Seeder
{
    public function run(): void
    {
        $locais = [
            [
                'nome' => 'Centro de Conferências Joaquim Chissano',
                'endereco' => 'Av. Marginal, 1234',
                'cidade' => 'Maputo',
                'provincia' => 'Maputo',
                'capacidade' => 5000,
                'descricao' => 'O maior centro de conferências de Moçambique.'
            ],
            [
                'nome' => 'Jardim Botânico de Maputo',
                'endereco' => 'Av. Ho Chi Min, 56',
                'cidade' => 'Maputo',
                'provincia' => 'Maputo',
                'capacidade' => 2000,
                'descricao' => 'Jardim botânico com áreas para eventos.'
            ],
            [
                'nome' => 'Pavilhão do Maxaquene',
                'endereco' => 'Av. Vladimir Lenine, 789',
                'cidade' => 'Maputo',
                'provincia' => 'Maputo',
                'capacidade' => 8000,
                'descricao' => 'Pavilhão desportivo para grandes eventos.'
            ],
            [
                'nome' => 'Beira Convention Center',
                'endereco' => 'Av. Marginal, 45',
                'cidade' => 'Beira',
                'provincia' => 'Sofala',
                'capacidade' => 3000,
                'descricao' => 'Centro de convenções na cidade da Beira.'
            ],
            [
                'nome' => 'Nampula Arena',
                'endereco' => 'Rua 1, 100',
                'cidade' => 'Nampula',
                'provincia' => 'Nampula',
                'capacidade' => 2000,
                'descricao' => 'Arena multiuso em Nampula.'
            ],
        ];

        foreach ($locais as $local) {
            Local::create($local);
        }
        
        $this->command->info('✅ Locais criados: ' . count($locais));
    }
}