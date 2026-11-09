<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('organizacao_id')->nullable()->constrained()->onDelete('set null');
            $table->string('acao', 100); // create, update, delete, login, logout, etc
            $table->string('tabela', 100); // eventos, usuarios, vendas, etc
            $table->bigInteger('registro_id')->nullable();
            $table->json('dados_anteriores')->nullable();
            $table->json('dados_novos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('rota')->nullable();
            $table->string('metodo', 10)->nullable();
            $table->timestamps();

            // Índices para consultas rápidas
            $table->index(['user_id', 'created_at']);
            $table->index(['organizacao_id', 'created_at']);
            $table->index(['tabela', 'registro_id']);
            $table->index(['acao', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditoria');
    }
};