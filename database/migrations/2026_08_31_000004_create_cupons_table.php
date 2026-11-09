<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupons', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('evento_id')->nullable()->constrained('eventos')->onDelete('cascade');
            $table->string('nome');
            $table->enum('tipo', ['percentual', 'fixo'])->default('percentual');
            $table->decimal('valor', 10, 2);
            $table->decimal('valor_minimo', 10, 2)->default(0);
            $table->integer('quantidade')->nullable();
            $table->integer('usos')->default(0);
            $table->timestamp('validade_inicio')->nullable();
            $table->timestamp('validade_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupons');
    }
};