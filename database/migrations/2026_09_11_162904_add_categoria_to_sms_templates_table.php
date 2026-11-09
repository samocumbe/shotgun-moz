<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            // Coluna `categoria` = tipo de negócio do template
            // (venda, lembrete, promocao, evento, outro)
            if (!Schema::hasColumn('sms_templates', 'categoria')) {
                $table->enum('categoria', ['venda', 'lembrete', 'promocao', 'evento', 'outro'])
                      ->default('outro')
                      ->after('assunto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            if (Schema::hasColumn('sms_templates', 'categoria')) {
                $table->dropColumn('categoria');
            }
        });
    }
};