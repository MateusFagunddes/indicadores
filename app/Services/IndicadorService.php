<?php

namespace App\Services;

class IndicadorService
{
    public function calcularAtingimento(float|int $realizado, float|int $meta, string $operador = '>='): float
    {
        if ($meta == 0) {
            return $this->atendeMeta($realizado, $meta, $operador) ? 100.0 : 0.0;
        }

        if ($operador === '=') {
            return round((1 - abs($realizado - $meta) / $meta) * 100, 2);
        }

        if (in_array($operador, ['<', '<='], true)) {
            if ($realizado == 0) {
                return 100.0;
            }

            return round(($meta / $realizado) * 100, 2);
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

    public function atendeMeta(float|int $realizado, float|int $meta, string $operador): bool
    {
        return match ($operador) {
            '>' => $realizado > $meta,
            '<' => $realizado < $meta,
            '=' => abs($realizado - $meta) < 0.005,
            '<=' => $realizado <= $meta,
            default => $realizado >= $meta,
        };
    }

    public function statusMeta(float|int $realizado, float|int $meta, string $operador = '>='): string
    {
        if ($this->atendeMeta($realizado, $meta, $operador)) {
            return 'Meta atingida';
        }

        $atingimento = $this->calcularAtingimento($realizado, $meta, $operador);

        if ($atingimento >= 80) {
            return 'Alerta';
        }

        return 'Abaixo da meta';
    }
}
