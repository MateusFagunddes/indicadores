<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    protected $model = Curso::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->randomElement([
                'Administração',
                'Direito',
                'Enfermagem',
                'Pedagogia',
                'Sistemas de Informação',
            ]),
            'modalidade' => fake()->randomElement(['Presencial', 'EAD', 'Híbrido']),
            'tipo' => fake()->randomElement(['Graduação', 'Pós-Graduação', 'Técnico']),
        ];
    }
}
