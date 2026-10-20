<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Indicador;
use App\Models\MetaIndicador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LancamentoTest extends TestCase
{
    use RefreshDatabase;

    private function indicador(string $unidade = 'quantidade'): Indicador
    {
        return Indicador::create(['codigo' => 'I'.uniqid(), 'nome' => 'Indicador', 'categoria' => 'Geral', 'unidade_medida' => $unidade]);
    }

    private function curso(): Curso
    {
        return Curso::create(['nome' => 'Curso '.uniqid(), 'modalidade' => 'EAD', 'tipo' => 'Graduação']);
    }

    public function test_coordenador_stores_launch_as_himself_for_linked_course(): void
    {
        $coordenador = User::factory()->create(['role' => 'coordenador']);
        $outro = User::factory()->create(['role' => 'admin']);
        $curso = $this->curso();
        $coordenador->cursos()->attach($curso);
        $indicador = $this->indicador();

        $this->actingAs($coordenador)->post('/lancamentos', [
            'curso_id' => $curso->id,
            'mes_ano' => now()->format('m/Y'),
            'indicador_id' => $indicador->id,
            'valor_meta' => '150',
            'user_id' => $outro->id,
        ])->assertRedirect('/lancamentos');

        $this->assertDatabaseHas('metas_indicadores', ['curso_id' => $curso->id, 'user_id' => $coordenador->id]);
    }

    public function test_coordenador_cannot_use_unlinked_course(): void
    {
        $coordenador = User::factory()->create(['role' => 'coordenador']);

        $this->actingAs($coordenador)->post('/lancamentos', [
            'curso_id' => $this->curso()->id,
            'mes_ano' => now()->format('m/Y'),
            'indicador_id' => $this->indicador()->id,
            'valor_meta' => '1',
        ])->assertSessionHasErrors('curso_id');
    }

    public function test_past_month_and_invalid_values_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $curso = $this->curso();
        $quantidade = $this->indicador();
        $monetario = $this->indicador('valor_monetario');

        $base = ['curso_id' => $curso->id, 'user_id' => $admin->id, 'mes_ano' => now()->format('m/Y')];

        $this->actingAs($admin)->post('/lancamentos', array_merge($base, ['mes_ano' => now()->subMonth()->format('m/Y'), 'indicador_id' => $quantidade->id, 'valor_meta' => '1']))
            ->assertSessionHasErrors('mes_ano');
        $this->actingAs($admin)->post('/lancamentos', array_merge($base, ['indicador_id' => $quantidade->id, 'valor_meta' => '1.5']))
            ->assertSessionHasErrors('valor_meta');
        $this->actingAs($admin)->post('/lancamentos', array_merge($base, ['indicador_id' => $monetario->id, 'valor_meta' => '1500.50']))
            ->assertSessionHasNoErrors();
    }

    public function test_duplicate_launch_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = ['curso_id' => $this->curso()->id, 'user_id' => $admin->id, 'mes_ano' => now()->format('m/Y'), 'indicador_id' => $this->indicador()->id, 'valor_meta' => '10'];

        $this->actingAs($admin)->post('/lancamentos', $payload)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post('/lancamentos', $payload)->assertSessionHasErrors('mes_ano');
    }

    public function test_past_launch_cannot_be_updated_or_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $meta = MetaIndicador::create([
            'curso_id' => $this->curso()->id,
            'indicador_id' => $this->indicador()->id,
            'user_id' => $admin->id,
            'mes_ano' => now()->subMonth()->format('m/Y'),
            'valor_meta' => 10,
        ]);

        $this->actingAs($admin)->delete("/lancamentos/{$meta->id}")->assertStatus(422);
        $this->actingAs($admin)->put("/lancamentos/{$meta->id}", [])->assertStatus(422);
        $this->assertDatabaseHas('metas_indicadores', ['id' => $meta->id]);
    }

    public function test_current_launch_can_be_updated_and_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $meta = MetaIndicador::create([
            'curso_id' => $this->curso()->id,
            'indicador_id' => $this->indicador()->id,
            'user_id' => $admin->id,
            'mes_ano' => now()->format('m/Y'),
            'valor_meta' => 10,
        ]);

        $this->actingAs($admin)->put("/lancamentos/{$meta->id}", [
            'curso_id' => $meta->curso_id,
            'indicador_id' => $meta->indicador_id,
            'user_id' => $admin->id,
            'mes_ano' => now()->format('m/Y'),
            'valor_meta' => '20',
        ])->assertSessionHasNoErrors();
        $this->assertSame(20, (int) $meta->fresh()->valor_meta);

        $this->actingAs($admin)->delete("/lancamentos/{$meta->id}")->assertRedirect('/lancamentos');
        $this->assertDatabaseMissing('metas_indicadores', ['id' => $meta->id]);
    }

    public function test_index_filters_by_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $curso = $this->curso();
        $indicador = $this->indicador();
        foreach ([now()->format('m/Y'), now()->addMonth()->format('m/Y')] as $mes) {
            MetaIndicador::create(['curso_id' => $curso->id, 'indicador_id' => $indicador->id, 'user_id' => $admin->id, 'mes_ano' => $mes, 'valor_meta' => 1]);
        }

        $this->actingAs($admin)->get('/lancamentos?mes_ano='.now()->format('m/Y'))
            ->assertInertia(fn ($page) => $page->component('Lancamentos')->has('items', 1));
    }
}
