<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Para duplicação de eventos
            if (!Schema::hasColumn('eventos', 'evento_pai_id')) {
                $table->foreignId('evento_pai_id')->nullable()->constrained('eventos')->onDelete('set null')->after('local_id');
            }
            
            // Para templates
            if (!Schema::hasColumn('eventos', 'template_id')) {
                $table->foreignId('template_id')->nullable()->constrained('eventos')->onDelete('set null')->after('evento_pai_id');
            }
            
            // Para eventos recorrentes
            if (!Schema::hasColumn('eventos', 'recorrencia')) {
                $table->enum('recorrencia', ['nenhuma', 'diario', 'semanal', 'mensal', 'anual'])->default('nenhuma')->after('status');
            }
            
            if (!Schema::hasColumn('eventos', 'recorrencia_intervalo')) {
                $table->integer('recorrencia_intervalo')->default(1)->after('recorrencia');
            }
            
            if (!Schema::hasColumn('eventos', 'recorrencia_data_fim')) {
                $table->date('recorrencia_data_fim')->nullable()->after('recorrencia_intervalo');
            }
            
            // Para templates
            if (!Schema::hasColumn('eventos', 'is_template')) {
                $table->boolean('is_template')->default(false)->after('recorrencia_data_fim');
            }
        });
    }

    public function down()
    {
        Schema::table('eventos', function (Blueprint $table) {
            $columns = ['evento_pai_id', 'template_id', 'recorrencia', 'recorrencia_intervalo', 'recorrencia_data_fim', 'is_template'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('eventos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};