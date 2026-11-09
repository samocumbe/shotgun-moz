<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'organizacao_id')) {
                $table->foreignId('organizacao_id')
                      ->nullable()
                      ->constrained('organizacoes')
                      ->onDelete('set null')
                      ->after('id');
            }
            
            if (!Schema::hasColumn('users', 'is_organizador')) {
                $table->boolean('is_organizador')->default(false)->after('organizacao_id');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'organizacao_id')) {
                $table->dropForeign(['organizacao_id']);
                $table->dropColumn('organizacao_id');
            }
            if (Schema::hasColumn('users', 'is_organizador')) {
                $table->dropColumn('is_organizador');
            }
        });
    }
};