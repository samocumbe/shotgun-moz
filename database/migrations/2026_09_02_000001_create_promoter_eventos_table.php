<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promoter_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoter_id')->constrained('promoters')->onDelete('cascade');
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->decimal('comissao_personalizada', 5, 2)->nullable(); // % específica para este evento
            $table->integer('ingressos_alocados')->default(0);
            $table->integer('ingressos_vendidos')->default(0);
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->timestamp('data_inicio')->nullable();
            $table->timestamp('data_fim')->nullable();
            $table->timestamps();
            
            $table->unique(['promoter_id', 'evento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoter_eventos');
    }
};