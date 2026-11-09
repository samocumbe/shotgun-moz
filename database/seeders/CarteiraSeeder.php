<?php

namespace Database\Seeders;

use App\Models\CarteiraIngresso;
use App\Models\User;
use App\Models\Ingresso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CarteiraSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $ingressos = Ingresso::where('status', 'pago')->limit(10)->get();

        foreach ($users as $user) {
            foreach ($ingressos as $ingresso) {
                CarteiraIngresso::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $user->id,
                    'ingresso_id' => $ingresso->id,
                    'codigo_acesso' => 'ACC-' . strtoupper(Str::random(10)),
                    'status' => ['ativo', 'ativo', 'ativo', 'utilizado'][rand(0, 3)],
                    'data_ativacao' => now()->subDays(rand(0, 10)),
                ]);
            }
        }
    }
}