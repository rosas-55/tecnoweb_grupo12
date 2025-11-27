<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VentaService
{
    public function paginate(?string $search = null, ?string $tipo = null, int $perPage = 15)
    {
        $query = Venta::with(['cliente', 'usuario', 'detalleVentas'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('cliente', function ($clienteQuery) use ($search) {
                    $clienteQuery->where('name', 'ilike', "%{$search}%")
                                 ->orWhere('email', 'ilike', "%{$search}%");
                })
                ->orWhere('total', 'ilike', "%{$search}%");
            });
        }

        if ($tipo) {
            $query->where('tipo', (int)$tipo);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            // Calcular total
            $total = 0;
            foreach ($data['detalles'] as $detalle) {
                $total += $detalle['precio_unitario'] * $detalle['cantidad'];
            }

            // Crear venta
            // Todas las ventas inician como pendientes (estado 0)
            // El estado cambiará a pagado (1) solo cuando se registre un pago que cubra el total
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'] ?? null,
                'usuario_id' => Auth::id(),
                'fecha' => $data['fecha'] ?? now(),
                'tipo' => (int)$data['tipo'],
                'total' => $total,
                'estado' => 0 // Siempre pendiente al crear, independientemente del tipo
            ]);

            // Crear detalles
            foreach ($data['detalles'] as $detalle) {
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'receta_id' => $detalle['receta_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['precio_unitario'] * $detalle['cantidad']
                ]);
            }

            return $venta;
        });
    }

    public function update(Venta $venta, array $data): Venta
    {
        return DB::transaction(function () use ($venta, $data) {
            // Calcular total
            $total = 0;
            foreach ($data['detalles'] as $detalle) {
                $total += $detalle['precio_unitario'] * $detalle['cantidad'];
            }

            // Actualizar venta
            // No cambiar el estado aquí, se actualiza automáticamente cuando se registran pagos
            $venta->update([
                'cliente_id' => $data['cliente_id'] ?? null,
                'fecha' => $data['fecha'] ?? now(),
                'tipo' => (int)$data['tipo'],
                'total' => $total,
                // No modificar el estado aquí, se calcula automáticamente según los pagos
            ]);

            // Eliminar detalles antiguos y crear nuevos
            $venta->detalles()->delete();
            foreach ($data['detalles'] as $detalle) {
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'receta_id' => $detalle['receta_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['precio_unitario'] * $detalle['cantidad']
                ]);
            }

            return $venta->fresh();
        });
    }
}
