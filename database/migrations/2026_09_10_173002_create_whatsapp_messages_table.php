<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🔥 CORREÇÃO: só cria se não existir
        if (!Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('organizacao_id')->nullable()->constrained('organizacoes')->nullOnDelete();
                $table->foreignId('evento_id')->nullable()->constrained('eventos')->nullOnDelete();
                $table->string('telefone', 20);
                $table->text('mensagem');
                $table->foreignId('template_id')->nullable();
                $table->string('status', 20)->default('pendente');
                $table->string('provedor', 50)->nullable();
                $table->string('referencia')->nullable();
                $table->timestamp('data_envio')->nullable();
                $table->timestamp('data_entrega')->nullable();
                $table->timestamp('data_leitura')->nullable();
                $table->integer('tentativas')->default(0);
                $table->text('erro')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('telefone');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};