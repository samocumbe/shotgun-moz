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
        Schema::create('dispositivos', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('nome');
            $table->enum('tipo', ['scanner', 'pos', 'supervisor', 'admin']);
            $table->foreignId('evento_id')->nullable()->constrained('eventos')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('ultimo_sync')->nullable();
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispositivos');
    }
};
