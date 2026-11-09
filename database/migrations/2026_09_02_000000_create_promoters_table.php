<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promoters', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('telefone');
            $table->string('nuit')->nullable();
            $table->string('codigo_promocional')->unique();
            $table->string('link_personalizado')->unique()->nullable();
            $table->string('qr_code')->nullable();
            $table->decimal('comissao_padrao', 5, 2)->default(10.00); // %
            $table->decimal('total_vendas', 10, 2)->default(0);
            $table->decimal('total_comissao', 10, 2)->default(0);
            $table->enum('status', ['ativo', 'inativo', 'pendente'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoters');
    }
};