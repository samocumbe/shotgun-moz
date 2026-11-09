<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ingressos', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('qr_code')->unique();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('categoria_ingresso_id')->constrained('categorias_ingressos')->onDelete('cascade');
            $table->foreignId('participante_id')->nullable()->constrained('participantes')->onDelete('set null');
            $table->decimal('preco_pago', 10, 2);
            $table->enum('status', ['disponivel', 'reservado', 'pago', 'utilizado', 'cancelado', 'reembolsado'])->default('disponivel');
            $table->timestamp('data_compra')->nullable();
            $table->timestamp('data_utilizacao')->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingressos');
    }
};
