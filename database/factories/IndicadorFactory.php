<?php

namespace Database\Factories;

use App\Models\Indicador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Indicador>
 */
class IndicadorFactory extends Factory
{
    protected $model = Indicador::class;

    public function definition(): array
    {
        $indicadores = [
            ['MATRICULAS', 'Matrículas', 'Acadêmico', 'quantidade'],
            ['EVASAO', 'Evasão', 'Permanência', 'porcentagem'],
            ['INADIMPLENCIA', 'Inadimplência', 'Financeiro', 'porcentagem'],
            ['TCC', 'TCC Concluídos', 'Acadêmico', 'quantidade'],
            ['REPROVACAO', 'Reprovação', 'Acadêmico', 'porcentagem'],
        ];

        [$codigo, $nome, $categoria, $unidadeMedida] = fake()->randomElement($indicadores);

        return [
            'codigo' => $codigo,
            'nome' => $nome,
            'categoria' => $categoria,
            'unidade_medida' => $unidadeMedida,
        ];
    }
}
