<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('registro_indicadores');

        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicador_id')->unique()->constrained('indicadores')->cascadeOnDelete();
            $table->string('operador', 2)->default('>=');
            $table->decimal('valor_meta', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
