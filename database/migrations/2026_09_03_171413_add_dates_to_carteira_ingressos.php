<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('carteira_ingressos', function (Blueprint $table) {
            // Adicionar colunas de data se não existirem
            if (!Schema::hasColumn('carteira_ingressos', 'data_transferencia')) {
                $table->timestamp('data_transferencia')->nullable()->after('data_utilizacao');
            }
            
            if (!Schema::hasColumn('carteira_ingressos', 'data_reembolso')) {
                $table->timestamp('data_reembolso')->nullable()->after('data_transferencia');
            }
        });
    }

    public function down()
    {
        Schema::table('carteira_ingressos', function (Blueprint $table) {
            if (Schema::hasColumn('carteira_ingressos', 'data_transferencia')) {
                $table->dropColumn('data_transferencia');
            }
            if (Schema::hasColumn('carteira_ingressos', 'data_reembolso')) {
                $table->dropColumn('data_reembolso');
            }
        });
    }
};