<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaIndicador extends Model
{
    protected $table = 'metas_indicadores';

    protected $fillable = ['indicador_id', 'unidade_id', 'curso_id',     'periodo_letivo_id', 'user_id', 'mes_ano', 'valor_meta'];

        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }
    public function indicador(): BelongsTo
    {
        return $this->belongsTo(Indicador::class);
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function periodoLetivo(): BelongsTo
    {
        return $this->belongsTo(PeriodoLetivo::class, 'periodo_letivo_id');
    }
}
