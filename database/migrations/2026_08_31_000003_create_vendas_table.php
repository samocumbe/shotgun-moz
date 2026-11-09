<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Vendedor
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->onDelete('set null');
            $table->foreignId('participante_id')->nullable()->constrained('participantes')->onDelete('set null');
            $table->enum('tipo', ['presencial', 'online', 'promoter'])->default('presencial');
            $table->decimal('valor_total', 10, 2);
            $table->enum('status', ['pendente', 'confirmada', 'cancelada'])->default('pendente');
            $table->timestamp('data_venda');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};