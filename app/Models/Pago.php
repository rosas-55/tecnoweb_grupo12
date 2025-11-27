<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $fillable = [
        'fechapago',
        'estado',
        'metodopago',
        'venta_id',
        'monto',
    ];

    protected $appends = ['qr_data'];

    /**
     * Obtener datos del QR (desde caché)
     */
    public function getQrDataAttribute(): ?array
    {
        $data = \Illuminate\Support\Facades\Cache::get("pago_qr_{$this->id}");
        return $data;
    }

    /**
     * Convertir a array incluyendo qr_data
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['qr_data'] = $this->qr_data;
        return $array;
    }

    /**
     * Guardar datos del QR en caché
     */
    public function setQrData(array $data): void
    {
        \Illuminate\Support\Facades\Cache::put("pago_qr_{$this->id}", $data, now()->addDays(30));
    }

    protected function casts(): array
    {
        return [
            'estado' => 'integer',
            'metodopago' => 'integer',
            'monto' => 'decimal:2',
        ];
    }

    /**
     * Venta a la que pertenece este pago
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }
}
