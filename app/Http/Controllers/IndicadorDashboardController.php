<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Indicador;
use App\Models\PeriodoLetivo;
use App\Models\Meta;
use App\Models\MetaIndicador;
use App\Models\Unidade;
use App\Models\User;
use App\Services\IndicadorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndicadorDashboardController extends Controller
{
    public function index(Request $request, IndicadorService $service)
    {
        $user = $request->user();
        $unidadeId = $request->query('unidade_id');
        $cursoId = $request->query('curso_id');
        $coordenadorId = $request->query('coordenador_id');
        $coordenadorId = $coordenadorId !== null ? (int) $coordenadorId : null;
        $periodoLetivoId = $request->query('periodo_letivo_id');
        $categoria = $request->query('categoria');

        $periodos = PeriodoLetivo::orderBy('ano')->orderBy('semestre')->get();
        $unidades = Unidade::orderBy('nome')->get();
        $coordenadores = User::query()->where('role', 'coordenador')->orderBy('name')->get();
        $cursosQuery = Curso::query();

        if ($user->isAdmin() && $coordenadorId) {
            $cursosQuery->whereHas('coordenadores', fn ($query) => $query->where('users.id', $coordenadorId));
        }

        if (! $user->isAdmin()) {
            $cursosQuery->whereIn('id', $user->cursos()->select('cursos.id'));
        }

        $cursos = $cursosQuery->orderBy('nome')->get();
        $cursoIds = $cursos->modelKeys();

        if (! $user->isAdmin()) {
            $cursoId = $cursoId && in_array((int) $cursoId, $cursoIds, true) ? $cursoId : null;
        }

        if ($user->isAdmin() && $coordenadorId) {
            $cursoId = $cursoId && in_array((int) $cursoId, $cursoIds, true) ? $cursoId : null;
        }

        $indicadores = Indicador::when($categoria, fn ($query) => $query->where('categoria', $categoria))->get();

        $registros = MetaIndicador::with(['indicador', 'unidade', 'curso', 'periodoLetivo'])
            ->when(! $user->isAdmin(), fn ($query) => $query->whereIn('curso_id', $cursoIds))
            ->when($user->isAdmin() && $coordenadorId, fn ($query) => $query->whereIn('curso_id', $cursos->pluck('id')))
            ->when($unidadeId, fn ($query) => $query->where('unidade_id', $unidadeId))
            ->when($cursoId, fn ($query) => $query->where('curso_id', $cursoId))
            ->when($periodoLetivoId, fn ($query) => $query->where('periodo_letivo_id', $periodoLetivoId))
            ->get();

        $metas = Meta::get()->keyBy('indicador_id');

        $lista = [];

        foreach ($indicadores as $indicador) {
            $indicadorRegistros = $registros->where('indicador_id', $indicador->id);
            $valorRealizado = (float) $indicadorRegistros->sum('valor_meta');
            $meta = (float) ($metas[$indicador->id]?->valor_meta ?? 0);
            $operador = $metas[$indicador->id]?->operador ?? '>=';
            $lista[] = [
                'indicador' => $indicador,
                'valor_realizado' => $valorRealizado,
                'meta' => $meta,
                'atingimento' => $service->calcularAtingimento($valorRealizado, max($meta, 1), $operador),
                'operador' => $operador,
                'status' => $service->statusMeta($valorRealizado, max($meta, 1), $operador),
            ];
        }

        return Inertia::render('Dashboard', [
            'filters' => [
                'unidade_id' => $unidadeId,
                'curso_id' => $cursoId,
                'coordenador_id' => $coordenadorId,
                'periodo_letivo_id' => $periodoLetivoId,
                'categoria' => $categoria,
            ],
            'unidades' => $unidades,
            'cursos' => $cursos,
            'coordenadores' => $coordenadores,
            'periodos' => $periodos,
            'indicadores' => $lista,
        ]);
    }

    public function rotativo(Request $request, IndicadorService $service)
    {
        $user = $request->user();
        $indicadores = Indicador::orderBy('nome')->get();
        $metas = Meta::get()->keyBy('indicador_id');

        $lancamentos = MetaIndicador::query()
            ->select(['id', 'indicador_id', 'curso_id', 'mes_ano', 'valor_meta'])
            ->with(['curso:id,nome'])
            ->when(! $user->isAdmin(), fn ($query) => $query->whereIn('curso_id', $user->cursos()->select('cursos.id')))
            ->get();

        $porIndicador = $lancamentos->groupBy('indicador_id');

        $lista = $indicadores->map(function (Indicador $indicador) use ($porIndicador, $metas, $service) {
            $registros = $porIndicador->get($indicador->id, collect());

            $labels = $registros->pluck('mes_ano')
                ->unique()
                ->sortBy(fn (string $mesAno) => substr($mesAno, 3, 4).substr($mesAno, 0, 2))
                ->values()
                ->all();

            $datasets = $registros->groupBy('curso_id')
                ->map(function ($registrosCurso, $cursoId) use ($labels) {
                    $porMes = $registrosCurso->keyBy('mes_ano');

                    return [
                        'curso_id' => (int) $cursoId,
                        'curso' => $registrosCurso->first()->curso?->nome ?? 'Curso '.$cursoId,
                        'data' => array_map(
                            fn (string $mesAno) => isset($porMes[$mesAno]) ? (float) $porMes[$mesAno]->valor_meta : null,
                            $labels
                        ),
                    ];
                })
                ->sortBy('curso')
                ->values()
                ->all();

            $valorRealizado = (float) $registros->sum('valor_meta');
            $meta = (float) ($metas[$indicador->id]?->valor_meta ?? 0);
            $operador = $metas[$indicador->id]?->operador ?? '>=';

            return [
                'indicador' => $indicador,
                'labels' => $labels,
                'datasets' => $datasets,
                'valor_realizado' => $valorRealizado,
                'meta' => $meta,
                'operador' => $operador,
                'atingimento' => $service->calcularAtingimento($valorRealizado, max($meta, 1), $operador),
                'status' => $service->statusMeta($valorRealizado, max($meta, 1), $operador),
            ];
        });

        return Inertia::render('DashboardRotativo', [
            'indicadores' => $lista,
            'intervaloRotacao' => 30,
        ]);
    }

    public function analitico(Request $request, Indicador $indicador, IndicadorService $service)
    {
        $user = $request->user();
        $ano = $request->query('ano');
        $ano = is_string($ano) && preg_match('/^\d{4}$/', $ano) ? $ano : null;
        $cursoId = $request->query('curso_id');
        $acumulado = $request->boolean('acumulado');

        $cursos = $user->isAdmin()
            ? Curso::orderBy('nome')->get(['id', 'nome'])
            : $user->cursos()->orderBy('nome')->get(['cursos.id', 'cursos.nome']);

        if (! $user->isAdmin()) {
            $cursoId = $cursoId && in_array((int) $cursoId, $cursos->modelKeys(), true) ? $cursoId : null;
        }

        $base = MetaIndicador::query()
            ->select(['id', 'indicador_id', 'curso_id', 'mes_ano', 'valor_meta'])
            ->with(['curso:id,nome'])
            ->where('indicador_id', $indicador->id)
            ->when(! $user->isAdmin(), fn ($query) => $query->whereIn('curso_id', $cursos->modelKeys()));

        $anos = (clone $base)->pluck('mes_ano')
            ->map(fn (string $mesAno) => substr($mesAno, 3, 4))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $registros = (clone $base)
            ->when($ano, fn ($query) => $query->where('mes_ano', 'like', '%/'.$ano))
            ->when($cursoId, fn ($query) => $query->where('curso_id', $cursoId))
            ->get();

        $labels = $registros->pluck('mes_ano')
            ->unique()
            ->sortBy(fn (string $mesAno) => substr($mesAno, 3, 4).substr($mesAno, 0, 2))
            ->values()
            ->all();

        $datasets = $registros->groupBy('curso_id')
            ->map(function ($registrosCurso, $cursoId) use ($labels, $acumulado) {
                $porMes = $registrosCurso->keyBy('mes_ano');
                $totalAcumulado = 0.0;

                $data = array_map(function (string $mesAno) use ($porMes, $acumulado, &$totalAcumulado) {
                    $valor = isset($porMes[$mesAno]) ? (float) $porMes[$mesAno]->valor_meta : null;

                    if (! $acumulado) {
                        return $valor;
                    }

                    if ($valor !== null) {
                        $totalAcumulado += $valor;
                    }

                    return $totalAcumulado;
                }, $labels);

                return [
                    'curso_id' => (int) $cursoId,
                    'curso' => $registrosCurso->first()->curso?->nome ?? 'Curso '.$cursoId,
                    'data' => $data,
                ];
            })
            ->sortBy('curso')
            ->values()
            ->all();

        $meta = Meta::where('indicador_id', $indicador->id)->first();
        $valorRealizado = (float) $registros->sum('valor_meta');
        $valorMeta = (float) ($meta?->valor_meta ?? 0);
        $operador = $meta?->operador ?? '>=';

        return Inertia::render('IndicadorAnalitico', [
            'indicador' => $indicador,
            'filters' => [
                'ano' => $ano,
                'curso_id' => $cursoId,
                'acumulado' => $acumulado,
            ],
            'anos' => $anos,
            'cursos' => $cursos,
            'labels' => $labels,
            'datasets' => $datasets,
            'meta' => $valorMeta,
            'operador' => $operador,
            'valor_realizado' => $valorRealizado,
            'atingimento' => $service->calcularAtingimento($valorRealizado, max($valorMeta, 1), $operador),
            'status' => $service->statusMeta($valorRealizado, max($valorMeta, 1), $operador),
        ]);
    }
}
