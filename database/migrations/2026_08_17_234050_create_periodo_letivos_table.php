<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_letivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('ano');
            $table->unsignedInteger('semestre');
            $table->string('rotulo');
            $table->timestamps();

            $table->unique(['ano', 'semestre']);
            $table->index('rotulo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_letivos');
    }
};
