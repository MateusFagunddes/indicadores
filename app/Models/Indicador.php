<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicador extends Model
{
    protected $table = 'indicadores';

    protected $fillable = ['codigo', 'nome', 'categoria', 'unidade_medida'];

    public function metasIndicadores(): HasMany
    {
        return $this->hasMany(MetaIndicador::class);
    }

    public function registrosIndicadores(): HasMany
    {
        return $this->hasMany(RegistroIndicador::class);
    }
}
