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
        Schema::table('users', function (Blueprint $table) {
            // Adicionar role (se não existir)
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'operador', 'promoter', 'cliente'])
                      ->default('cliente')
                      ->after('email');
            }

            // Adicionar is_admin (se não existir)
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('role');
            }

            // Adicionar telefone (se não existir)
            if (!Schema::hasColumn('users', 'telefone')) {
                $table->string('telefone')->nullable()->after('is_admin');
            }

            // Adicionar NUIT (se não existir)
            if (!Schema::hasColumn('users', 'nuit')) {
                $table->string('nuit')->nullable()->after('telefone');
            }

            // Adicionar endereco (se não existir)
            if (!Schema::hasColumn('users', 'endereco')) {
                $table->text('endereco')->nullable()->after('nuit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['role', 'is_admin', 'telefone', 'nuit', 'endereco'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};