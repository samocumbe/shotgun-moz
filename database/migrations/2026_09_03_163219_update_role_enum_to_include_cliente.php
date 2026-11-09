<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Para MariaDB/MySQL, a maneira mais segura é usar SQL puro
        // para modificar o ENUM
        
        // Primeiro, adicionar a nova coluna com os valores atualizados
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role_new', ['admin', 'operador', 'promoter', 'cliente'])
                  ->default('cliente')
                  ->after('role');
        });

        // Copiar os dados da coluna antiga para a nova
        \DB::statement('UPDATE users SET role_new = role');

        // Remover a coluna antiga
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        // Renomear a nova coluna para o nome original (usando SQL puro)
        \DB::statement('ALTER TABLE users CHANGE role_new role ENUM("admin","operador","promoter","cliente") NOT NULL DEFAULT "cliente"');
    }

    public function down()
    {
        // Reverter: criar a coluna antiga com os valores originais
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role_old', ['admin', 'operador', 'promoter'])
                  ->default('operador')
                  ->after('is_admin');
        });

        \DB::statement('UPDATE users SET role_old = role');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        \DB::statement('ALTER TABLE users CHANGE role_old role ENUM("admin","operador","promoter") NOT NULL DEFAULT "operador"');
    }
};