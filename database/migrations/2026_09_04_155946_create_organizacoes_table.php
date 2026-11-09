<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('organizacoes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nome', 255);
            $table->string('slug', 255)->unique();
            $table->text('descricao')->nullable();
            $table->string('email', 255)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->text('endereco')->nullable();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->enum('status', ['ativo', 'inativo', 'suspenso'])->default('ativo');
            $table->timestamps();

            // Índices
            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizacoes');
    }
};