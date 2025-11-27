<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventario extends Model
{
    /**
     * Indica si el modelo debe usar timestamps
     */
    public $timestamps = false;

    protected $fillable = [
        'insumo_id',
        'tipo_movimiento',
        'cantidad',
        'fecha',
        'metodo_inventario',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'fecha' => 'datetime',
        ];
    }

    /**
     * Insumo relacionado
     */
    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }
}
