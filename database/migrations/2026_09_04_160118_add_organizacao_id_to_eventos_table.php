<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('eventos', function (Blueprint $table) {
            if (!Schema::hasColumn('eventos', 'organizacao_id')) {
                $table->foreignId('organizacao_id')
                      ->nullable()
                      ->constrained('organizacoes')
                      ->onDelete('set null')
                      ->after('local_id');
            }
        });
    }

    public function down()
    {
        Schema::table('eventos', function (Blueprint $table) {
            if (Schema::hasColumn('eventos', 'organizacao_id')) {
                $table->dropForeign(['organizacao_id']);
                $table->dropColumn('organizacao_id');
            }
        });
    }
};