<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carteira_ingressos', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ingresso_id')->constrained('ingressos')->onDelete('cascade');
            $table->string('codigo_acesso')->unique();
            $table->enum('status', ['ativo', 'utilizado', 'transferido', 'cancelado'])->default('ativo');
            $table->timestamp('data_ativacao')->nullable();
            $table->timestamp('data_utilizacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carteira_ingressos');
    }
};