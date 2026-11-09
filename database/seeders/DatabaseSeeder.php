<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocalSeeder::class,
            EventoSeeder::class,
            CategoriaIngressoSeeder::class,
            CupomSeeder::class,
            VendaSeeder::class,
            PromoterSeeder::class,
	    NotificacaoSeeder::class,
        ]);
        
        $this->command->info('🎉 Todos os dados de teste foram criados com sucesso!');
    }
}