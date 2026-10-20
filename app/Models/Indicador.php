<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Indicador extends Model
{
    protected $table = 'indicadores';

    protected $fillable = ['codigo', 'nome', 'categoria', 'unidade_medida'];

    public function metasIndicadores(): HasMany
    {
        return $this->hasMany(MetaIndicador::class);
    }

    public function meta(): HasOne
    {
        return $this->hasOne(Meta::class);
    }
}
