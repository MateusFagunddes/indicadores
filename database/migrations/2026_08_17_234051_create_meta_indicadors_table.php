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
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->foreignId('periodo_letivo_id')->constrained('periodos_letivos')->cascadeOnDelete();
            $table->decimal('valor_meta', 12, 2);
            $table->timestamps();

            $table->index(['indicador_id', 'periodo_letivo_id']);
            $table->index(['unidade_id', 'curso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas_indicadores');
    }
};
