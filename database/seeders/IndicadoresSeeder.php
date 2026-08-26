<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Indicador;
use App\Models\MetaIndicador;
use App\Models\PeriodoLetivo;
use App\Models\RegistroIndicador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Database\Seeder;

class IndicadoresSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['nome' => 'Campus Getúlio Vargas', 'codigo' => 'GV'],
            ['nome' => 'Campus Passo Fundo', 'codigo' => 'PF'],
            ['nome' => 'Campus Vacaria', 'codigo' => 'VC'],
            ['nome' => 'Campus Carazinho', 'codigo' => 'CZ'],
        ];

        foreach ($unidades as $unidade) {
            Unidade::firstOrCreate($unidade);
        }

        $cursos = [
            ['nome' => 'Administração', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Pedagogia', 'modalidade' => 'EAD', 'tipo' => 'Graduação'],
            ['nome' => 'Sistemas de Informação', 'modalidade' => 'Híbrido', 'tipo' => 'Graduação'],
            ['nome' => 'Direito', 'modalidade' => 'Presencial', 'tipo' => 'Pós-Graduação'],
        ];

        foreach ($cursos as $curso) {
            Curso::firstOrCreate($curso);
        }

        $cursoAdministracao = Curso::where('nome', 'Administração')->firstOrFail();
        $cursoPedagogia = Curso::where('nome', 'Pedagogia')->firstOrFail();

        $admin = User::updateOrCreate(
            ['email' => 'admin@unideau.local'],
            ['name' => 'Administrador', 'role' => 'admin', 'password' => 'password']
        );

        $coordenadorAdministracao = User::updateOrCreate(
            ['email' => 'coordenador.adm@unideau.local'],
            ['name' => 'Coordenador de Administração', 'role' => 'coordenador', 'password' => 'password']
        );

        $coordenadorPedagogia = User::updateOrCreate(
            ['email' => 'coordenador.ped@unideau.local'],
            ['name' => 'Coordenador de Pedagogia', 'role' => 'coordenador', 'password' => 'password']
        );

        $admin->cursos()->sync([]);
        $coordenadorAdministracao->cursos()->sync([$cursoAdministracao->id]);
        $coordenadorPedagogia->cursos()->sync([$cursoPedagogia->id]);

        $periodos = [
            ['ano' => 2024, 'semestre' => 1, 'rotulo' => '2024/1'],
            ['ano' => 2024, 'semestre' => 2, 'rotulo' => '2024/2'],
            ['ano' => 2025, 'semestre' => 1, 'rotulo' => '2025/1'],
            ['ano' => 2025, 'semestre' => 2, 'rotulo' => '2025/2'],
            ['ano' => 2026, 'semestre' => 1, 'rotulo' => '2026/1'],
        ];

        foreach ($periodos as $periodo) {
            PeriodoLetivo::firstOrCreate($periodo);
        }

        $indicadores = [
            ['codigo' => 'MATRICULAS', 'nome' => 'Matrículas', 'categoria' => 'Acadêmico', 'unidade_medida' => 'quantidade'],
            ['codigo' => 'EVASAO', 'nome' => 'Evasão', 'categoria' => 'Permanência', 'unidade_medida' => 'porcentagem'],
            ['codigo' => 'INADIMPLENCIA', 'nome' => 'Inadimplência', 'categoria' => 'Financeiro', 'unidade_medida' => 'porcentagem'],
            ['codigo' => 'TCC', 'nome' => 'TCC Concluídos', 'categoria' => 'Acadêmico', 'unidade_medida' => 'quantidade'],
        ];

        foreach ($indicadores as $indicador) {
            Indicador::firstOrCreate(['codigo' => $indicador['codigo']], $indicador);
        }

        $periodoAtual = PeriodoLetivo::where('rotulo', '2026/1')->firstOrFail();
        $unidadeList = Unidade::all();
        $cursoList = Curso::all();
        $indicadorList = Indicador::all();

        foreach ($indicadorList as $indicador) {
            foreach ($unidadeList as $unidade) {
                foreach ($cursoList as $curso) {
                    MetaIndicador::firstOrCreate(
                        [
                            'indicador_id' => $indicador->id,
                            'unidade_id' => $unidade->id,
                            'curso_id' => $curso->id,
                            'periodo_letivo_id' => $periodoAtual->id,
                        ],
                        [
                            'valor_meta' => random_int(100, 2000),
                        ]
                    );
                }
            }
        }

        foreach ($indicadorList as $indicador) {
            foreach ($unidadeList as $unidade) {
                foreach ($cursoList as $curso) {
                    for ($mes = 1; $mes <= 12; $mes++) {
                        RegistroIndicador::updateOrCreate(
                            [
                                'indicador_id' => $indicador->id,
                                'unidade_id' => $unidade->id,
                                'curso_id' => $curso->id,
                                'periodo_letivo_id' => $periodoAtual->id,
                                'mes' => $mes,
                            ],
                            [
                                'valor_realizado' => fake()->randomFloat(2, 50, 1800),
                                'observacoes' => 'Dados iniciais do dashboard',
                            ]
                        );
                    }
                }
            }
        }
    }
}

