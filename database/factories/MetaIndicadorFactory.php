<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Indicador;
use App\Models\MetaIndicador;
use App\Models\PeriodoLetivo;
use App\Models\Unidade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetaIndicador>
 */
class MetaIndicadorFactory extends Factory
{
    protected $model = MetaIndicador::class;

    public function definition(): array
    {
        return [
            'indicador_id' => Indicador::factory(),
            'unidade_id' => Unidade::factory(),
            'curso_id' => Curso::factory(),
            'periodo_letivo_id' => PeriodoLetivo::factory(),
            'valor_meta' => fake()->randomFloat(2, 100, 2000),
        ];
    }
}
