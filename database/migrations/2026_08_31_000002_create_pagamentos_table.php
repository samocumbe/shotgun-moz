<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->decimal('valor', 10, 2);
            $table->enum('metodo', ['mpesa', 'emola', 'dinheiro', 'cartao', 'transferencia']);
            $table->enum('status', ['pendente', 'confirmado', 'falha', 'cancelado', 'reembolsado'])->default('pendente');
            $table->string('referencia')->nullable();
            $table->string('codigo_confirmacao')->nullable();
            $table->json('dados_pagamento')->nullable(); // Dados do gateway
            $table->timestamp('data_confirmacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};