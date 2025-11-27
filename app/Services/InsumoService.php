<?php

namespace App\Services;

use App\Models\Insumo;

class InsumoService
{
    public function paginate(?string $search = null, int $perPage = 15)
    {
        $query = Insumo::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhere('unidad_medida', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function create(array $data): Insumo
    {
        return Insumo::create($data);
    }

    public function update(Insumo $insumo, array $data): Insumo
    {
        $insumo->update($data);
        return $insumo;
    }

    public function delete(Insumo $insumo): bool
    {
        return $insumo->delete();
    }
}
