<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produccion extends Model
{
    /**
     * Indica si el modelo debe gestionar timestamps automáticamente
     */
    public $timestamps = false;

    protected $table = 'produccion';

    protected $fillable = [
        'receta_id',
        'cantidad_producida',
        'fecha',
        'responsable_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_producida' => 'integer',
            'fecha' => 'datetime',
        ];
    }

    /**
     * Receta producida
     */
    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class);
    }

    /**
     * Usuario responsable de la producción
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
