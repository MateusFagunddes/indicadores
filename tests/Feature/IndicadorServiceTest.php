<?php

namespace Tests\Feature;

use App\Services\IndicadorService;
use Tests\TestCase;

class IndicadorServiceTest extends TestCase
{
    public function test_calcula_atingimento_e_variacao(): void
    {
        $service = new IndicadorService();

        $this->assertSame(120.0, $service->calcularAtingimento(120, 100));
        $this->assertSame(25.0, $service->calcularVariacao(100, 80));
        $this->assertSame('Meta atingida', $service->statusMeta(120, 100));
    }

    public function test_atingimento_respeita_operador_maior_ou_igual(): void
    {
        $service = new IndicadorService();

        $this->assertSame(95.0, $service->calcularAtingimento(19, 20, '>='));
        $this->assertSame(108.7, $service->calcularAtingimento(25, 23, '>='));
        $this->assertSame('Abaixo da meta', $service->statusMeta(10, 20, '>='));
        $this->assertSame('Alerta', $service->statusMeta(19, 20, '>='));
        $this->assertSame('Meta atingida', $service->statusMeta(20, 20, '>='));
        $this->assertSame('Meta atingida', $service->statusMeta(25, 20, '>='));
    }

    public function test_atingimento_respeita_operador_menor_ou_igual(): void
    {
        $service = new IndicadorService();

        // Inadimplência: meta <= 50000. Quanto menor o realizado, melhor.
        $this->assertSame(125.0, $service->calcularAtingimento(40000, 50000, '<='));
        $this->assertSame(83.33, $service->calcularAtingimento(60000, 50000, '<='));
        $this->assertSame(50.0, $service->calcularAtingimento(100000, 50000, '<='));
        $this->assertSame(100.0, $service->calcularAtingimento(0, 50000, '<='));

        $this->assertSame('Meta atingida', $service->statusMeta(40000, 50000, '<='));
        $this->assertSame('Meta atingida', $service->statusMeta(50000, 50000, '<='));
        $this->assertSame('Meta atingida', $service->statusMeta(0, 50000, '<='));
        $this->assertSame('Alerta', $service->statusMeta(60000, 50000, '<='));
        $this->assertSame('Abaixo da meta', $service->statusMeta(100000, 50000, '<='));
    }

    public function test_atingimento_respeita_operadores_estritos_e_igualdade(): void
    {
        $service = new IndicadorService();

        $this->assertSame('Meta atingida', $service->statusMeta(21, 20, '>'));
        $this->assertSame('Alerta', $service->statusMeta(20, 20, '>'));
        $this->assertSame('Meta atingida', $service->statusMeta(49999, 50000, '<'));
        $this->assertSame('Alerta', $service->statusMeta(50000, 50000, '<'));
        $this->assertSame('Meta atingida', $service->statusMeta(100, 100, '='));
        $this->assertSame('Abaixo da meta', $service->statusMeta(150, 100, '='));
    }

    public function test_meta_zero_nao_gera_percentual_distorcido(): void
    {
        $service = new IndicadorService();

        $this->assertSame(100.0, $service->calcularAtingimento(10, 0, '>='));
        $this->assertSame(0.0, $service->calcularAtingimento(10, 0, '<='));
        $this->assertSame(100.0, $service->calcularAtingimento(0, 0, '<='));
        $this->assertSame('Meta atingida', $service->statusMeta(10, 0, '>='));
        $this->assertSame('Abaixo da meta', $service->statusMeta(10, 0, '<='));
    }
}
