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
        Schema::create('sync_queues', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('dispositivo_id')->constrained('dispositivos')->onDelete('cascade');
            $table->string('tipo');
            $table->string('entidade');
            $table->unsignedBigInteger('entidade_id');
            $table->json('payload');
            $table->enum('status', ['pendente', 'processando', 'sucesso', 'falha'])->default('pendente');
            $table->integer('tentativas')->default(0);
            $table->timestamp('synced_at')->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_queues');
    }
};
