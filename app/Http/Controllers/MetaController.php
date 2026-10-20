<?php

namespace App\Http\Controllers;

use App\Models\Indicador;
use App\Models\Meta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MetaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Metas', [
            'items' => Meta::query()
                ->with('indicador:id,codigo,nome,unidade_medida')
                ->get()
                ->sortBy(fn (Meta $meta) => $meta->indicador->nome)
                ->values()
                ->map(fn (Meta $meta) => [
                    'id' => $meta->id,
                    'indicador' => $meta->indicador,
                    'operador' => $meta->operador,
                    'valor_meta' => $meta->valor_meta,
                ]),
            'indicadores' => Indicador::query()->orderBy('nome')->get(['id', 'codigo', 'nome', 'unidade_medida']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Meta::create($this->validated($request));

        return to_route('admin.metas')->with('success', 'Meta salva com sucesso.');
    }

    public function update(Request $request, Meta $meta): RedirectResponse
    {
        $meta->update($this->validated($request, $meta));

        return to_route('admin.metas')->with('success', 'Meta atualizada com sucesso.');
    }

    public function destroy(Meta $meta): RedirectResponse
    {
        $meta->delete();

        return to_route('admin.metas')->with('success', 'Meta excluída com sucesso.');
    }

    private function validated(Request $request, ?Meta $atual = null): array
    {
        $indicador = Indicador::find($request->input('indicador_id'));

        $valorRegex = $indicador?->unidade_medida === 'quantidade'
            ? '/^\d{1,10}$/'
            : '/^\d{1,13}(\.\d{1,2})?$/';

        return $request->validate([
            'indicador_id' => ['required', 'integer', Rule::exists('indicadores', 'id'), Rule::unique('metas', 'indicador_id')->ignore($atual?->id)],
            'operador' => ['required', Rule::in(['>', '<', '=', '<=', '>='])],
            'valor_meta' => ['required', 'regex:'.$valorRegex],
        ], [
            'indicador_id.unique' => 'Já existe uma meta para este indicador.',
            'valor_meta.regex' => $indicador?->unidade_medida === 'quantidade'
                ? 'Informe um número inteiro de até 10 dígitos.'
                : 'Informe um valor com até 13 dígitos inteiros e 2 casas decimais.',
        ]);
    }
}
