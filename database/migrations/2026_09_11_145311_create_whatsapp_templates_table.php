<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organizacao_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nome', 100);
            $table->string('codigo', 100)->unique();
            $table->text('mensagem');
            $table->json('variaveis')->nullable();
            $table->enum('categoria', ['marketing', 'utilidade', 'autenticacao'])->default('utilidade');
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organizacao_id', 'status']);
            $table->index('is_active');
            $table->index('codigo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};