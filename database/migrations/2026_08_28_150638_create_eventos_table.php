<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();  // ← ADICIONAR ESTA LINHA
            $table->foreignId('local_id')->constrained('locais')->onDelete('cascade');
            $table->string('nome');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->integer('capacidade_total')->default(0);
            $table->integer('ingressos_vendidos')->default(0);
            $table->enum('status', ['rascunho', 'publicado', 'encerrado', 'cancelado'])->default('rascunho');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};