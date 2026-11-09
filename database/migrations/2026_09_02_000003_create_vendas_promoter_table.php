<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas_promoter', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('promoter_id')->constrained('promoters')->onDelete('cascade');
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->string('codigo_promocional_usado');
            $table->decimal('valor_total', 10, 2);
            $table->decimal('comissao_gerada', 10, 2);
            $table->enum('status', ['confirmada', 'pendente', 'cancelada'])->default('confirmada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendas_promoter');
    }
};