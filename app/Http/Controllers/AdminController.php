<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Indicador;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function users(Request $request): Response
    {
        return $this->resourcePage('Usuários', 'Gerencie administradores, coordenadores e seus acessos.', 'users', User::query()->with('cursos')->orderBy('name')->get(), $request);
    }

    public function cursos(Request $request): Response
    {
        return $this->resourcePage('Cursos', 'Cadastre cursos e vincule coordenadores responsáveis.', 'cursos', Curso::query()->with('coordenadores:id,name')->withCount('coordenadores')->orderBy('nome')->get(), $request, [
            'coordenadores' => User::query()->where('role', 'coordenador')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function indicadores(Request $request): Response
    {
        return $this->resourcePage('Indicadores', 'Cadastre indicadores, metas e regras de acompanhamento.', 'indicadores', Indicador::query()->orderBy('nome')->get(), $request);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'coordenador'])],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create($data);
        return to_route('admin.users')->with('success', 'Usuário criado com sucesso.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'coordenador'])],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $user->update($data);
        return to_route('admin.users')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        abort_if($user->is(request()->user()), 422, 'Você não pode excluir o próprio usuário.');
        $user->delete();
        return to_route('admin.users')->with('success', 'Usuário excluído com sucesso.');
    }

    public function storeCurso(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'modalidade' => ['required', Rule::in(['Presencial', 'EAD', 'Híbrido'])],
            'tipo' => ['required', Rule::in(['Graduação', 'Pós-Graduação', 'Técnico'])],
            'coordenador_ids' => ['nullable', 'array'],
            'coordenador_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', 'coordenador')],
        ]);
        $coordenadorIds = $data['coordenador_ids'] ?? [];
        unset($data['coordenador_ids']);
        $curso = Curso::create($data);
        $curso->coordenadores()->sync($coordenadorIds);
        return to_route('admin.cursos')->with('success', 'Curso criado com sucesso.');
    }

    public function updateCurso(Request $request, Curso $curso): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'modalidade' => ['required', Rule::in(['Presencial', 'EAD', 'Híbrido'])],
            'tipo' => ['required', Rule::in(['Graduação', 'Pós-Graduação', 'Técnico'])],
            'coordenador_ids' => ['nullable', 'array'],
            'coordenador_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', 'coordenador')],
        ]);
        $coordenadorIds = $data['coordenador_ids'] ?? [];
        unset($data['coordenador_ids']);
        $curso->update($data);
        $curso->coordenadores()->sync($coordenadorIds);
        return to_route('admin.cursos')->with('success', 'Curso atualizado com sucesso.');
    }

    public function destroyCurso(Curso $curso): RedirectResponse
    {
        $curso->delete();
        return to_route('admin.cursos')->with('success', 'Curso excluído com sucesso.');
    }

    public function storeIndicador(Request $request): RedirectResponse
    {
        Indicador::create($request->validate([
            'codigo' => ['required', 'string', 'max:255', 'unique:indicadores,codigo'],
            'nome' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:255'],
            'unidade_medida' => ['required', Rule::in(['quantidade', 'porcentagem', 'valor_monetario'])],
        ]));
        return to_route('admin.indicadores')->with('success', 'Indicador criado com sucesso.');
    }

    public function updateIndicador(Request $request, Indicador $indicador): RedirectResponse
    {
        $indicador->update($request->validate([
            'codigo' => ['required', 'string', 'max:255', Rule::unique('indicadores', 'codigo')->ignore($indicador)],
            'nome' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:255'],
            'unidade_medida' => ['required', Rule::in(['quantidade', 'porcentagem', 'valor_monetario'])],
        ]));
        return to_route('admin.indicadores')->with('success', 'Indicador atualizado com sucesso.');
    }

    public function destroyIndicador(Indicador $indicador): RedirectResponse
    {
        $indicador->delete();
        return to_route('admin.indicadores')->with('success', 'Indicador excluído com sucesso.');
    }

    private function resourcePage(string $title, string $description, string $resource, $items, Request $request, array $extra = []): Response
    {
        return Inertia::render('Admin/Resource', [
            'title' => $title,
            'description' => $description,
            'resource' => $resource,
            'items' => $items,
            'editing' => $request->integer('edit') ?: null,
            ...$extra,
        ]);
    }
}
