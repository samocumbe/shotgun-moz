<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locais', function (Blueprint $table) {
            $table->id();
            $table->string('nome');              // ← COLUNA NOME
            $table->string('endereco');          // ← COLUNA ENDERECO
            $table->string('cidade');            // ← COLUNA CIDADE
            $table->string('provincia');         // ← COLUNA PROVINCIA
            $table->integer('capacidade');       // ← COLUNA CAPACIDADE
            $table->text('descricao')->nullable(); // ← COLUNA DESCRICAO
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locais');
    }
};