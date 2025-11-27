<?php

namespace App\Services;

use App\Models\Receta;

class RecetaService
{
    public function paginate(?string $search = null, int $perPage = 15)
    {
        $query = Receta::with('insumos');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function create(array $data): Receta
    {
        $receta = Receta::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'tiempo_preparacion' => $data['tiempo_preparacion'] ?? null,
            'estado' => 1
        ]);

        // Adjuntar insumos con cantidades
        if (isset($data['insumos']) && is_array($data['insumos'])) {
            $insumosData = [];
            foreach ($data['insumos'] as $insumo) {
                $insumosData[$insumo['insumo_id']] = [
                    'cantidad' => $insumo['cantidad']
                ];
            }
            $receta->insumos()->sync($insumosData);
        }

        return $receta;
    }

    public function update(Receta $receta, array $data): Receta
    {
        $receta->update([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? $receta->descripcion,
            'tiempo_preparacion' => $data['tiempo_preparacion'] ?? $receta->tiempo_preparacion
        ]);

        // Actualizar insumos
        if (isset($data['insumos']) && is_array($data['insumos'])) {
            $insumosData = [];
            foreach ($data['insumos'] as $insumo) {
                $insumosData[$insumo['insumo_id']] = [
                    'cantidad' => $insumo['cantidad']
                ];
            }
            $receta->insumos()->sync($insumosData);
        }

        return $receta;
    }

    public function delete(Receta $receta): bool
    {
        $receta->insumos()->detach();
        return $receta->delete();
    }
}
