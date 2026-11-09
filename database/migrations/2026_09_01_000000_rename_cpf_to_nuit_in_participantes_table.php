<?php

/**
 * ============================================================
 * MIGRATION: Renomear CPF para NUIT
 * ============================================================
 * 
 * Esta migration renomeia a coluna 'cpf' para 'nuit'
 * na tabela 'participantes' para refletir a terminologia
 * usada em Moçambique.
 * 
 * NUIT = Número Único de Identificação Tributária (9 dígitos)
 * ============================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Renomeia a coluna 'cpf' para 'nuit'
     */
    public function up(): void
    {
        Schema::table('participantes', function (Blueprint $table) {
            $table->renameColumn('cpf', 'nuit');
        });
    }

    /**
     * Reverse the migrations.
     * Reverte a coluna 'nuit' para 'cpf'
     */
    public function down(): void
    {
        Schema::table('participantes', function (Blueprint $table) {
            $table->renameColumn('nuit', 'cpf');
        });
    }
};