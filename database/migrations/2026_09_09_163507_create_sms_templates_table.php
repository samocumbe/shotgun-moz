<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organizacao_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nome', 100);
            $table->string('assunto', 200)->nullable();
            $table->text('mensagem');
            $table->enum('tipo', ['sms', 'whatsapp'])->default('sms');
            $table->json('variaveis')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organizacao_id', 'tipo']);
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_templates');
    }
};