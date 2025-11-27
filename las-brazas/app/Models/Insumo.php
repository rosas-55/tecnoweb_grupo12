<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insumo extends Model
{
    // Ajuste al nombre real de la tabla en la BD
    protected $table = 'insumos';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'unidad_medida',
        'stock_minimo',
        'stock_actual',
        'costo_unitario',
        'estado',
    ];

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'insumo_id');
    }

    public function recetas(): BelongsToMany
    {
        return $this->belongsToMany(Receta::class, 'receta_insumo', 'insumo_id', 'receta_id')
            ->withPivot('cantidad');
    }
}


