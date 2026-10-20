<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Indicador;
use App\Models\MetaIndicador;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LancamentoController extends Controller
{
    private const MES_ANO_REGEX = '/^(0[1-9]|1[0-2])\/\d{4}$/';

    public function index(Request $request): Response
    {
        $user = $request->user();
        $filtro = $request->query('mes_ano');
        $filtro = is_string($filtro) && preg_match(self::MES_ANO_REGEX, $filtro) ? $filtro : null;

        $itens = MetaIndicador::query()
            ->with(['curso:id,nome', 'indicador:id,codigo,nome,unidade_medida', 'user:id,name'])
            ->when(! $user->isAdmin(), fn ($query) => $query->whereIn('curso_id', $this->cursoIdsDoUsuario($user)))
            ->when($filtro, fn ($query) => $query->where('mes_ano', $filtro))
            ->orderByRaw('substr(mes_ano, 4, 4) desc, substr(mes_ano, 1, 2) desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn (MetaIndicador $meta) => [
                'id' => $meta->id,
                'mes_ano' => $meta->mes_ano,
                'curso' => $meta->curso,
                'indicador' => $meta->indicador,
                'user' => $meta->user,
                'valor_meta' => $meta->valor_meta,
                'editable' => $this->mesAnoVigenteOuFuturo($meta->mes_ano),
            ]);

        return Inertia::render('Lancamentos', [
            'items' => $itens,
            'cursos' => $user->isAdmin()
                ? Curso::query()->orderBy('nome')->get(['id', 'nome'])
                : $user->cursos()->orderBy('nome')->get(['cursos.id', 'cursos.nome']),
            'indicadores' => Indicador::query()->orderBy('nome')->get(['id', 'codigo', 'nome', 'unidade_medida']),
            'usuarios' => $user->isAdmin()
                ? User::query()->orderBy('name')->get(['id', 'name'])
                : collect([$user->only('id', 'name')]),
            'mesAnoAtual' => now()->format('m/Y'),
            'filtroMesAno' => $filtro,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        MetaIndicador::create($this->validated($request));

        return to_route('lancamentos')->with('success', 'Lançamento salvo com sucesso.');
    }

    public function update(Request $request, MetaIndicador $lancamento): RedirectResponse
    {
        $this->authorizeChange($request, $lancamento);
        $lancamento->update($this->validated($request, $lancamento));

        return to_route('lancamentos')->with('success', 'Lançamento atualizado com sucesso.');
    }

    public function destroy(Request $request, MetaIndicador $lancamento): RedirectResponse
    {
        $this->authorizeChange($request, $lancamento);
        $lancamento->delete();

        return to_route('lancamentos')->with('success', 'Lançamento excluído com sucesso.');
    }

    private function validated(Request $request, ?MetaIndicador $atual = null): array
    {
        $user = $request->user();
        $indicador = Indicador::find($request->input('indicador_id'));

        $valorRules = ['required'];
        if ($indicador?->unidade_medida === 'quantidade') {
            $valorRules[] = 'regex:/^\d{1,10}$/';
        } else {
            $valorRules[] = 'regex:/^\d{1,13}(\.\d{1,2})?$/';
        }

        $cursoRule = Rule::exists('cursos', 'id');
        if (! $user->isAdmin()) {
            $cursoRule = $cursoRule->whereIn('id', $this->cursoIdsDoUsuario($user));
        }

        $data = $request->validate([
            'curso_id' => ['required', 'integer', $cursoRule],
            'mes_ano' => [
                'required',
                'regex:'.self::MES_ANO_REGEX,
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! $this->mesAnoVigenteOuFuturo($value)) {
                        $fail('O mês/ano deve ser igual ou posterior ao mês atual.');
                    }
                },
                Rule::unique('metas_indicadores', 'mes_ano')
                    ->where('indicador_id', $request->input('indicador_id'))
                    ->where('curso_id', $request->input('curso_id'))
                    ->ignore($atual?->id),
            ],
            'indicador_id' => ['required', 'integer', Rule::exists('indicadores', 'id')],
            'valor_meta' => $valorRules,
            'user_id' => [$user->isAdmin() ? 'required' : 'nullable', 'integer', Rule::exists('users', 'id')],
        ], [
            'mes_ano.unique' => 'Já existe um lançamento para este curso, indicador e mês/ano.',
            'mes_ano.regex' => 'Informe o mês/ano no formato mm/aaaa.',
            'valor_meta.regex' => $indicador?->unidade_medida === 'quantidade'
                ? 'Informe um número inteiro de até 10 dígitos.'
                : 'Informe um valor com até 13 dígitos inteiros e 2 casas decimais.',
        ]);

        if (! $user->isAdmin()) {
            $data['user_id'] = $user->id;
        }

        return $data;
    }

    private function authorizeChange(Request $request, MetaIndicador $lancamento): void
    {
        $user = $request->user();

        abort_unless(
            $user->isAdmin() || in_array($lancamento->curso_id, $this->cursoIdsDoUsuario($user), true),
            403
        );
        abort_unless($this->mesAnoVigenteOuFuturo($lancamento->mes_ano), 422, 'Lançamentos de meses anteriores não podem ser alterados.');
    }

    private function cursoIdsDoUsuario(User $user): array
    {
        return $user->cursos()->pluck('cursos.id')->all();
    }

    private function mesAnoVigenteOuFuturo(mixed $mesAno): bool
    {
        if (! is_string($mesAno) || ! preg_match(self::MES_ANO_REGEX, $mesAno)) {
            return false;
        }

        return Carbon::createFromFormat('!m/Y', $mesAno)->greaterThanOrEqualTo(now()->startOfMonth());
    }
}
