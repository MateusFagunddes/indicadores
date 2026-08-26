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
}
