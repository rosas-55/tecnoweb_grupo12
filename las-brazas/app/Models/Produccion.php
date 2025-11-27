<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produccion extends Model
{
    protected $table = 'produccion';
    public $timestamps = false;

    protected $fillable = [
        'cantidad_producida',
        'fecha',
        'receta_id',
    ];

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }
}


