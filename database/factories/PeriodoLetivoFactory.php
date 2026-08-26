<?php

namespace Database\Factories;

use App\Models\PeriodoLetivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodoLetivo>
 */
class PeriodoLetivoFactory extends Factory
{
    protected $model = PeriodoLetivo::class;

    public function definition(): array
    {
        $ano = fake()->numberBetween(2024, 2026);
        $semestre = fake()->randomElement([1, 2]);

        return [
            'ano' => $ano,
            'semestre' => $semestre,
            'rotulo' => $ano . '/' . $semestre,
        ];
    }
}
