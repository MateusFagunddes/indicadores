<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meta extends Model
{
    protected $table = 'metas';

    protected $fillable = ['indicador_id', 'operador', 'valor_meta'];

    public function indicador(): BelongsTo
    {
        return $this->belongsTo(Indicador::class);
    }
}
