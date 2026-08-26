<?php

namespace Database\Factories;

use App\Models\Unidade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unidade>
 */
class UnidadeFactory extends Factory
{
    protected $model = Unidade::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->randomElement([
                'Campus Getúlio Vargas',
                'Campus Passo Fundo',
                'Campus Vacaria',
                'Campus Carazinho',
            ]),
            'codigo' => fake()->unique()->bothify('UNI-##'),
        ];
    }
}
