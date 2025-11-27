<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    // Si en tu BD está en plural, cambia a 'detalle_ventas'
    protected $table = 'detalle_venta';
    public $timestamps = false;

    protected $fillable = [
        'cantidad',
        'precio_unitario',
        'sub_total',
        'venta_id',
        'receta_id',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }
}


