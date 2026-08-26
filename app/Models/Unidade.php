<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidade extends Model
{
    protected $fillable = ['nome', 'codigo'];

    public function metasIndicadores(): HasMany
    {
        return $this->hasMany(MetaIndicador::class);
    }

    public function registrosIndicadores(): HasMany
    {
        return $this->hasMany(RegistroIndicador::class);
    }
}
