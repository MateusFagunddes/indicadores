<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_coordinator_has_only_related_courses(): void
    {
        $coordenador = User::factory()->create(['role' => 'coordenador']);
        $cursoPermitido = Curso::create([
            'nome' => 'Curso permitido',
            'modalidade' => 'Presencial',
            'tipo' => 'Graduação',
        ]);
        $cursoBloqueado = Curso::create([
            'nome' => 'Curso bloqueado',
            'modalidade' => 'EAD',
            'tipo' => 'Técnico',
        ]);

        $coordenador->cursos()->attach($cursoPermitido);

        $this->assertTrue($coordenador->cursos->contains($cursoPermitido));
        $this->assertFalse($coordenador->cursos->contains($cursoBloqueado));
    }

    public function test_coordinator_cannot_access_admin_registration_routes(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'coordenador']))
            ->get('/admin/cursos')
            ->assertForbidden();
    }

    public function test_admin_can_filter_dashboard_by_coordinator(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coordenador = User::factory()->create(['role' => 'coordenador']);

        $cursoDoCoordenador = Curso::create([
            'nome' => 'Curso do coordenador',
            'modalidade' => 'Presencial',
            'tipo' => 'Graduação',
        ]);

        $cursoOutro = Curso::create([
            'nome' => 'Outro curso',
            'modalidade' => 'EAD',
            'tipo' => 'Técnico',
        ]);

        $coordenador->cursos()->attach($cursoDoCoordenador);

        $this->actingAs($admin)
            ->get('/?coordenador_id=' . $coordenador->id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('filters.coordenador_id', $coordenador->id)
                ->where('cursos', function ($cursos) use ($cursoDoCoordenador, $cursoOutro) {
                    $cursos = collect($cursos);

                    $this->assertTrue($cursos->contains('nome', $cursoDoCoordenador->nome));
                    $this->assertFalse($cursos->contains('nome', $cursoOutro->nome));

                    return true;
                })
            );
    }
}
