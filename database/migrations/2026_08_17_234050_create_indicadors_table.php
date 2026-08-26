<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicadores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nome');
            $table->string('categoria');
            $table->enum('unidade_medida', ['quantidade', 'porcentagem', 'valor_monetario']);
            $table->timestamps();

            $table->index('categoria');
            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicadores');
    }
};
