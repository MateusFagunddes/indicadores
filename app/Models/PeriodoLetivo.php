<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoLetivo extends Model
{
    protected $table = 'periodos_letivos';

    protected $fillable = ['ano', 'semestre', 'rotulo'];

    public function metasIndicadores(): HasMany
    {
        return $this->hasMany(MetaIndicador::class);
    }

    public function registrosIndicadores(): HasMany
    {
        return $this->hasMany(RegistroIndicador::class);
    }
}
