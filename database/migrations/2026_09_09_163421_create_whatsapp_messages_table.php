<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('organizacao_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('evento_id')->nullable()->constrained()->onDelete('set null');
            $table->string('telefone', 20);
            $table->text('mensagem');
            $table->string('template_id')->nullable();
            $table->enum('status', ['pendente', 'enviado', 'falha', 'entregue', 'lido'])->default('pendente');
            $table->string('provedor', 50)->nullable();
            $table->string('referencia', 100)->nullable();
            $table->timestamp('data_envio')->nullable();
            $table->timestamp('data_entrega')->nullable();
            $table->timestamp('data_leitura')->nullable();
            $table->integer('tentativas')->default(0);
            $table->text('erro')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['organizacao_id', 'status']);
            $table->index(['evento_id', 'status']);
            $table->index(['telefone', 'status']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};