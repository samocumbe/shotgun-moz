<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        \DB::statement("ALTER TABLE carteira_ingressos MODIFY COLUMN status ENUM('ativo', 'utilizado', 'transferido', 'reembolsado') NOT NULL DEFAULT 'ativo'");
    }

    public function down()
    {
        \DB::statement("ALTER TABLE carteira_ingressos MODIFY COLUMN status ENUM('ativo', 'utilizado', 'reembolsado') NOT NULL DEFAULT 'ativo'");
    }
};