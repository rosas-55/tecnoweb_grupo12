<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receta extends Model
{
    protected $table = 'recetas';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tiempo_preparacion',
        'estado',
    ];

    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'receta_insumo', 'receta_id', 'insumo_id')
            ->withPivot('cantidad');
    }

    public function producciones(): HasMany
    {
        return $this->hasMany(Produccion::class, 'receta_id');
    }
}


