<?php

namespace App\Services;

use App\Models\Produccion;
use App\Models\Receta;
use App\Models\Insumo;
use App\Models\Inventario;
use Illuminate\Support\Facades\Auth;

class ProduccionService
{
    public function paginate($search = null, $perPage = 10)
    {
        $query = Produccion::with(['receta.insumos', 'responsable'])->orderBy('fecha', 'desc');

        if ($search) {
            $query->whereHas('receta', function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%");
            })->orWhereHas('responsable', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Produccion
    {
        $receta = Receta::with('insumos')->find($data['receta_id']);

        // Verificar disponibilidad de insumos
        foreach ($receta->insumos as $insumo) {
            $cantidadNecesaria = $insumo->pivot->cantidad * $data['cantidad_producida'];

            if ($insumo->stock_actual < $cantidadNecesaria) {
                throw new \Exception("Stock insuficiente de {$insumo->nombre}. Disponible: {$insumo->stock_actual}, Necesario: {$cantidadNecesaria}");
            }
        }

        // Crear producción
        $produccion = Produccion::create([
            'receta_id' => $data['receta_id'],
            'cantidad_producida' => $data['cantidad_producida'],
            'responsable_id' => $data['responsable_id'] ?? Auth::id(),
            'fecha' => $data['fecha'] ?? now()
        ]);

        // Descontar insumos del inventario
        foreach ($receta->insumos as $insumo) {
            $cantidadUsada = $insumo->pivot->cantidad * $data['cantidad_producida'];

            Inventario::create([
                'insumo_id' => $insumo->id,
                'tipo_movimiento' => 'salida',
                'cantidad' => $cantidadUsada,
                'fecha' => now(),
                'observacion' => "Producción de {$receta->nombre}",
                'metodo_inventario' => "PROD-{$produccion->id}"
            ]);

            $insumo->stock_actual -= $cantidadUsada;
            $insumo->save();
        }

        return $produccion;
    }
}
