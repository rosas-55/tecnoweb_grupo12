<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventario extends Model
{
    // Nombre real de la tabla en la BD
    protected $table = 'inventarios';
    public $timestamps = false;

    protected $fillable = [
        'tipo_movimiento',
        'cantidad',
        'fecha',
        'metodo_inventario',
        'observacion',
        'insumo_id',
    ];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}


