<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Indicador;
use App\Models\PeriodoLetivo;
use App\Models\RegistroIndicador;
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

        $registros = RegistroIndicador::with(['indicador', 'unidade', 'curso', 'periodoLetivo'])
            ->when(! $user->isAdmin(), fn ($query) => $query->whereIn('curso_id', $cursoIds))
            ->when($user->isAdmin() && $coordenadorId, fn ($query) => $query->whereIn('curso_id', $cursos->pluck('id')))
            ->when($unidadeId, fn ($query) => $query->where('unidade_id', $unidadeId))
            ->when($cursoId, fn ($query) => $query->where('curso_id', $cursoId))
            ->when($periodoLetivoId, fn ($query) => $query->where('periodo_letivo_id', $periodoLetivoId))
            ->get();

        $lista = [];

        foreach ($indicadores as $indicador) {
            $indicadorRegistros = $registros->where('indicador_id', $indicador->id);
            $valorRealizado = (float) $indicadorRegistros->sum('valor_realizado');
            $meta = (float) (
                $indicadorRegistros
                    ->map(fn ($registro) => $registro->valor_realizado * 0.8)
                    ->sum()
            );

            $lista[] = [
                'indicador' => $indicador,
                'valor_realizado' => $valorRealizado,
                'meta' => $meta,
                'atingimento' => $service->calcularAtingimento($valorRealizado, max($meta, 1)),
                'status' => $service->statusMeta($valorRealizado, max($meta, 1)),
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
}
