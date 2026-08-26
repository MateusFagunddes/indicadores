<?php

namespace App\Services;

class IndicadorService
{
    public function calcularAtingimento(float|int $realizado, float|int $meta): float
    {
        if ($meta == 0) {
            return 0.0;
        }

        return round(($realizado / $meta) * 100, 2);
    }

    public function calcularVariacao(float|int $atual, float|int $anterior): float
    {
        if ($anterior == 0) {
            return 0.0;
        }

        return round((($atual - $anterior) / $anterior) * 100, 2);
    }

    public function statusMeta(float|int $realizado, float|int $meta): string
    {
        $atingimento = $this->calcularAtingimento($realizado, $meta);

        if ($atingimento >= 100) {
            return 'Meta atingida';
        }

        if ($atingimento >= 80) {
            return 'Alerta';
        }

        return 'Abaixo da meta';
    }
}
