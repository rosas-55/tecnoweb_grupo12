<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'fecha',
        'tipo',
        'total',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'tipo' => 'integer',
            'total' => 'decimal:2',
            'estado' => 'integer',
        ];
    }

    /**
     * Cliente que realizó la compra
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Usuario que registró la venta
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Detalles de la venta
     */
    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    /**
     * Alias para detalles (para compatibilidad)
     */
    public function detalles(): HasMany
    {
        return $this->detalleVentas();
    }

    /**
     * Pagos de esta venta
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    /**
     * Verificar si es venta al contado
     */
    public function esContado(): bool
    {
        return $this->tipo === 1;
    }

    /**
     * Verificar si es venta a crédito
     */
    public function esCredito(): bool
    {
        return $this->tipo === 2;
    }
}
