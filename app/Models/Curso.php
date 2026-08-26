<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Curso extends Model
{
    protected $fillable = ['nome', 'modalidade', 'tipo'];

    public function metasIndicadores(): HasMany
    {
        return $this->hasMany(MetaIndicador::class);
    }

    public function registrosIndicadores(): HasMany
    {
        return $this->hasMany(RegistroIndicador::class);
    }

    public function coordenadores(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->where('role', 'coordenador');
    }
}
