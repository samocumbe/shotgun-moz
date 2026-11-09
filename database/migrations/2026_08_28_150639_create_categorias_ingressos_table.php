<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categorias_ingressos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->string('nome');
            $table->string('slug');
            $table->text('descricao')->nullable();
            $table->decimal('preco', 10, 2)->default(0);
            $table->integer('quantidade_total')->default(0);
            $table->integer('quantidade_vendida')->default(0);
            $table->enum('tipo', ['normal', 'vip', 'backstage', 'meia', 'estudante'])->default('normal');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias_ingressos');
    }
};
