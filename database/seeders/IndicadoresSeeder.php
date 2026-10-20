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
            // BACHARELADO (Graduação)
            ['nome' => 'Administração + Ciências Contábeis', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Agronomia', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Arquitetura e Urbanismo', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Biblioteconomia', 'modalidade' => 'EAD', 'tipo' => 'Graduação'],
            ['nome' => 'Ciência da Computação', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Direito', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Educação Física', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Eng. Civil', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Eng. de Produção', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Eng. Mecânica', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Farmácia + Biomedicina', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Fisioterapia', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Jornalismo + Publicidade e Propaganda', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Medicina Veterinária', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Odontologia', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Psicologia', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Medicina', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Enfermagem', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],

            // LICENCIATURA (Graduação)
            ['nome' => 'Artes', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Ciências Biológicas', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Ciências Sociais', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Filosofia', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Física', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Geografia', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'História', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Letras', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Matemática', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Pedagogia', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Sociologia', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],

            // TECNÓLOGO (Graduação)
            ['nome' => 'Análise e Desenvolvimento de Sistemas', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Estética e Cosmética', 'modalidade' => 'Presencial', 'tipo' => 'Graduação'],
            ['nome' => 'Secretariado', 'modalidade' => 'EAD', 'tipo' => 'Graduação'],

            // TÉCNICO
            ['nome' => 'Técnico em Enfermagem', 'modalidade' => 'Presencial', 'tipo' => 'Técnico'],
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
            // Indicadores Acadêmicos / Alunos
            ['codigo' => 'ALUNOS_GRADUACAO', 'nome' => 'Nº Alunos Graduação', 'categoria' => 'Acadêmico', 'unidade_medida' => 'quantidade'],
            ['codigo' => 'ALUNOS_POS', 'nome' => 'Nº Alunos Pós-Graduação', 'categoria' => 'Acadêmico', 'unidade_medida' => 'quantidade'],
            ['codigo' => 'ALUNOS_CURSOS_LIVRES', 'nome' => 'Nº Alunos em Cursos Livres', 'categoria' => 'Acadêmico', 'unidade_medida' => 'quantidade'],
            ['codigo' => 'CURSOS_POS_ANDAMENTO', 'nome' => 'Quant. Cursos de Pós em Andamento', 'categoria' => 'Acadêmico', 'unidade_medida' => 'quantidade'],
            ['codigo' => 'CURSOS_LIVRES_ANDAMENTO', 'nome' => 'Quant. Cursos Livres em Andamento', 'categoria' => 'Acadêmico', 'unidade_medida' => 'quantidade'],
            
            // Permanência / Evasão
            ['codigo' => 'EVASAO_AUTOMATICA', 'nome' => 'Evasão Automática', 'categoria' => 'Permanência', 'unidade_medida' => 'quantidade'], // Ajuste para 'porcentagem' se preferir

            // Financeiro
            ['codigo' => 'ALUNOS_INADIMPLENTES', 'nome' => 'Nº Alunos Inadimplentes (Devedores)', 'categoria' => 'Financeiro', 'unidade_medida' => 'quantidade'],
            ['codigo' => 'VALOR_INADIMPLENCIA', 'nome' => 'Valor Total de Inadimplência', 'categoria' => 'Financeiro', 'unidade_medida' => 'valor_monetario'],
            ['codigo' => 'RECEITA_LIQUIDA_TOTAL', 'nome' => 'Receita Líquida Total', 'categoria' => 'Financeiro', 'unidade_medida' => 'valor_monetario'],
            ['codigo' => 'CUSTO_TOTAL_CURSO', 'nome' => 'Custo Total do Curso', 'categoria' => 'Financeiro', 'unidade_medida' => 'valor_monetario'],

            // Institucional / Operacional / Extensão
            ['codigo' => 'ESCOLAS_VISITADAS', 'nome' => 'Quantidade de Escolas Visitadas', 'categoria' => 'Institucional', 'unidade_medida' => 'quantidade'],
            ['codigo' => 'EGRESSOS_VISITADOS', 'nome' => 'Quantidade de Egressos Visitados', 'categoria' => 'Institucional', 'unidade_medida' => 'quantidade'],
            ['codigo' => 'NAO_CONFORMIDADES', 'nome' => 'Quantidade de Não-Conformidades', 'categoria' => 'Qualidade', 'unidade_medida' => 'quantidade'],
            ['codigo' => 'ACOES_COMUNIDADE', 'nome' => 'Quantidade de Ações na Comunidade', 'categoria' => 'Extensão', 'unidade_medida' => 'quantidade'],
        ];

        foreach ($indicadores as $indicador) {
            Indicador::firstOrCreate(['codigo' => $indicador['codigo']], $indicador);
        }

        $periodoAtual = PeriodoLetivo::where('rotulo', '2026/1')->firstOrFail();
        $unidadeList = Unidade::all();
        $cursoList = Curso::all();
        $indicadorList = Indicador::all();

        foreach ($indicadorList as $indicador) {
            foreach ($cursoList as $curso) {
                MetaIndicador::firstOrCreate(
                    [
                        'indicador_id' => $indicador->id,
                        'curso_id' => $curso->id,
                        'mes_ano' => now()->format('m/Y'),
                    ],
                    [
                        'periodo_letivo_id' => $periodoAtual->id,
                        'valor_meta' => random_int(100, 2000),
                    ]
                );
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

