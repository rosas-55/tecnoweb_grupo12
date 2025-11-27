<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Insumo extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'categoria',
        'descripcion',
        'unidad_medida',
        'stock_minimo',
        'stock_actual',
        'costo_unitario',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'stock_minimo' => 'decimal:2',
            'stock_actual' => 'decimal:2',
            'costo_unitario' => 'decimal:2',
            'estado' => 'integer',
        ];
    }

    /**
     * Movimientos de inventario de este insumo
     */
    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }

    /**
     * Recetas que usan este insumo
     */
    public function recetas(): BelongsToMany
    {
        return $this->belongsToMany(Receta::class, 'receta_insumo')
            ->withPivot('cantidad');
    }
}
