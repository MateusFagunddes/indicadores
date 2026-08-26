<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_indicadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicador_id')->constrained('indicadores')->cascadeOnDelete();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->foreignId('periodo_letivo_id')->constrained('periodos_letivos')->cascadeOnDelete();
            $table->unsignedTinyInteger('mes')->nullable();
            $table->decimal('valor_realizado', 12, 2);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index(['indicador_id', 'periodo_letivo_id']);
            $table->index(['unidade_id', 'curso_id']);
            $table->index('mes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_indicadores');
    }
};
