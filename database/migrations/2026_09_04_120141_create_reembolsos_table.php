<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reembolsos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('carteira_ingresso_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('valor_reembolso', 10, 2);
            $table->string('motivo', 255);
            $table->text('observacoes')->nullable();
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado', 'processado'])->default('pendente');
            $table->timestamp('data_solicitacao')->nullable();
            $table->timestamp('data_aprovacao')->nullable();
            $table->timestamp('data_processamento')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reembolsos');
    }
};