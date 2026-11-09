<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('carteira_ingresso_id')->constrained('carteira_ingressos')->onDelete('cascade');
            $table->foreignId('usuario_origem_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('usuario_destino_id')->constrained('users')->onDelete('cascade');
            $table->string('codigo_confirmacao')->unique();
            $table->enum('status', ['pendente', 'confirmado', 'cancelado'])->default('pendente');
            $table->timestamp('data_confirmacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};