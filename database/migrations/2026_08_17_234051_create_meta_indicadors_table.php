<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas_indicadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicador_id')->constrained('indicadores')->cascadeOnDelete();
            $table->foreignId('unidade_id')->nullable()->constrained('unidades')->nullOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->foreignId('periodo_letivo_id')->nullable()->constrained('periodos_letivos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mes_ano', 7);
            $table->decimal('valor_meta', 15, 2);
            $table->timestamps();

            $table->unique(['mes_ano', 'indicador_id', 'curso_id'], 'metas_mes_ano_indicador_curso_unique');
            $table->index(['indicador_id', 'periodo_letivo_id']);
            $table->index(['unidade_id', 'curso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas_indicadores');
    }
};
