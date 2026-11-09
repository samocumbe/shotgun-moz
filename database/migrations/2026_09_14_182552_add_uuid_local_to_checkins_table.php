<?php

/**
 * ============================================================
 * MIGRATION: adiciona uuid_local + sincronizado_em a checkins
 * ============================================================
 *
 * Estes campos suportam o modo offline:
 * - uuid_local       → UUID gerado no dispositivo, para deduplicar
 * - sincronizado_em  → timestamp em que o servidor confirmou o sync
 * ============================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aplica as colunas.
     */
    public function up(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            // UUID local do dispositivo (para deduplicação em sync)
            $table->string('uuid_local', 100)
                ->nullable()
                ->after('uuid');

            // Timestamp do último sync confirmado pelo servidor
            $table->timestamp('sincronizado_em')
                ->nullable()
                ->after('uuid_local');

            // Índice para acelerar pesquisas de sync
            $table->index('uuid_local', 'idx_checkins_uuid_local');
            $table->index('sincronizado_em', 'idx_checkins_sincronizado_em');
        });
    }

    /**
     * Reverte as colunas.
     */
    public function down(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            $table->dropIndex('idx_checkins_uuid_local');
            $table->dropIndex('idx_checkins_sincronizado_em');
            $table->dropColumn(['uuid_local', 'sincronizado_em']);
        });
    }
};