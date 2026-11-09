<?php

namespace Database\Seeders;

use App\Models\Notificacao;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificacaoSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $titulos = [
            'Novo evento publicado!',
            'Sua venda foi confirmada',
            'Ingresso validado com sucesso',
            'Promoção de ingressos',
            'Lembrete: evento amanhã',
        ];

        foreach ($users as $user) {
            for ($i = 0; $i < 5; $i++) {
                Notificacao::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $user->id,
                    'titulo' => $titulos[array_rand($titulos)],
                    'mensagem' => 'Esta é uma notificação de teste para o usuário ' . $user->name,
                    'tipo' => ['info', 'success', 'warning', 'danger'][rand(0, 3)],
                    'lida' => rand(0, 1) === 1,
                    'data_leitura' => rand(0, 1) === 1 ? now()->subHours(rand(1, 24)) : null,
                ]);
            }
        }
    }
}