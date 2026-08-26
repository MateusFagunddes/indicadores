<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Indicador;
use App\Models\PeriodoLetivo;
use App\Models\RegistroIndicador;
use App\Models\Unidade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistroIndicador>
 */
class RegistroIndicadorFactory extends Factory
{
    protected $model = RegistroIndicador::class;

    public function definition(): array
    {
        return [
            'indicador_id' => Indicador::factory(),
            'unidade_id' => Unidade::factory(),
            'curso_id' => Curso::factory(),
            'periodo_letivo_id' => PeriodoLetivo::factory(),
            'mes' => fake()->numberBetween(1, 12),
            'valor_realizado' => fake()->randomFloat(2, 50, 2500),
            'observacoes' => fake()->optional()->sentence(),
        ];
    }
}
