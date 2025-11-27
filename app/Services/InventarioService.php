<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\Inventario;

class InventarioService
{
    public function paginate(?string $search = null, ?string $tipo = null, int $perPage = 15)
    {
        $query = Inventario::with('insumo');

        if ($search) {
            $query->whereHas('insumo', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if ($tipo) {
            $query->where('tipo_movimiento', $tipo);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function create(array $data): Inventario
    {
        $inventario = Inventario::create($data);

        // Actualizar stock del insumo
        $insumo = Insumo::find($data['insumo_id']);

        if ($data['tipo_movimiento'] === 'ingreso') {
            $insumo->stock_actual += $data['cantidad'];
        } else {
            $insumo->stock_actual -= $data['cantidad'];
        }

        $insumo->save();

        return $inventario;
    }

    public function getKardex(Insumo $insumo)
    {
        return Inventario::where('insumo_id', $insumo->id)
            ->orderBy('fecha')
            ->get()
            ->map(function ($movimiento) {
                static $saldo = 0;

                if ($movimiento->tipo_movimiento === 'ingreso') {
                    $saldo += $movimiento->cantidad;
                } else {
                    $saldo -= $movimiento->cantidad;
                }

                $movimiento->saldo = $saldo;
                return $movimiento;
            });
    }
}
