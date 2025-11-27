<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    public $timestamps = false;

    protected $table = 'detalle_venta';

    protected $fillable = [
        'venta_id',
        'receta_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Venta a la que pertenece este detalle
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Receta vendida en este detalle
     */
    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class);
    }
}
