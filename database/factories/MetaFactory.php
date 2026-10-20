<?php

namespace Database\Factories;

use App\Models\Indicador;
use App\Models\Meta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meta>
 */
class MetaFactory extends Factory
{
    protected $model = Meta::class;

    public function definition(): array
    {
        return [
            'indicador_id' => Indicador::factory(),
            'operador' => fake()->randomElement(['>', '<', '=', '<=', '>=']),
            'valor_meta' => fake()->randomFloat(2, 100, 2000),
        ];
    }
}
