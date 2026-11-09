<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('promoter_id')->constrained('promoters')->onDelete('cascade');
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->decimal('valor_venda', 10, 2);
            $table->decimal('percentual_comissao', 5, 2);
            $table->decimal('valor_comissao', 10, 2);
            $table->enum('status', ['pendente', 'pago', 'cancelado'])->default('pendente');
            $table->timestamp('data_pagamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissoes');
    }
};