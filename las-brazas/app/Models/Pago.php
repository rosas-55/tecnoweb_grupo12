<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    // Nombre real de la tabla
    protected $table = 'pagos';
    public $timestamps = false;

    protected $fillable = [
        'fechapago',
        'estado',
        'metodopago',
        'monto',
        'venta_id',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}


