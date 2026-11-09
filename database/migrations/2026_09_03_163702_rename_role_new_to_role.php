<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Verificar se a coluna role_new existe e renomear para role
        if (Schema::hasColumn('users', 'role_new')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('role_new', 'role');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('role', 'role_new');
            });
        }
    }
};