<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            if (!Schema::hasColumn('sms', 'template_id')) {
                $table->foreignId('template_id')
                      ->nullable()
                      ->after('evento_id')
                      ->constrained('sms_templates')
                      ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            if (Schema::hasColumn('sms', 'template_id')) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            }
        });
    }
};