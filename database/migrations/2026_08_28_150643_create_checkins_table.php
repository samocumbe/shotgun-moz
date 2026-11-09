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
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('ingresso_id')->constrained('ingressos')->onDelete('cascade');
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('dispositivo_id')->nullable()->constrained('dispositivos')->onDelete('set null');
            $table->string('qr_code');
            $table->timestamp('data_checkin');
            $table->string('porta_acesso')->nullable();
            $table->string('localizacao')->nullable();
            $table->enum('status', ['pendente', 'sincronizado'])->default('pendente');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
