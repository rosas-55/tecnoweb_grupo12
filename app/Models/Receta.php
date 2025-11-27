<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receta extends Model
{
    /**
     * La tabla no tiene columnas created_at/updated_at en la BD existente.
     */
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tiempo_preparacion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'tiempo_preparacion' => 'integer',
            'estado' => 'integer',
        ];
    }

    /**
     * Insumos que se usan en esta receta
     */
    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'receta_insumo')
            ->withPivot('cantidad');
    }

    /**
     * Producciones de esta receta
     */
    public function producciones(): HasMany
    {
        return $this->hasMany(Produccion::class);
    }

    /**
     * Detalles de venta que incluyen esta receta
     */
    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }
}
