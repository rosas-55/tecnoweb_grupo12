<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Produccion;
use App\Models\Insumo;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    public function getEstadisticasGenerales(): array
    {
        return [
            'ventas_dia' => Venta::whereDate('fecha', today())->sum('total'),
            'ventas_mes' => Venta::whereMonth('fecha', now()->month)->sum('total'),
            'ventas_pendientes' => Venta::where('estado', 0)->count(),
            'stock_bajo' => Insumo::whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'producciones_mes' => Produccion::whereMonth('fecha', now()->month)->count(),
            'productos_activos' => Insumo::where('stock_actual', '>', 0)->count()
        ];
    }

    public function reporteVentas(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $fechaInicio = $fechaInicio ?? now()->startOfMonth()->toDateString();
        $fechaFin = $fechaFin ?? now()->endOfMonth()->toDateString();
        
        $ventas = Venta::with(['cliente', 'detalleVentas'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();
        
        return [
            'ventas' => $ventas,
            'total_ventas' => $ventas->sum('total'),
            'promedio_venta' => $ventas->avg('total'),
            'ventas_por_tipo' => [
                'contado' => $ventas->where('tipo', 1)->sum('total'),
                'credito' => $ventas->where('tipo', 2)->sum('total')
            ],
            'productos_mas_vendidos' => $this->getProductosMasVendidos($fechaInicio, $fechaFin)
        ];
    }

    public function reporteInventario(): array
    {
        $insumos = Insumo::all();
        
        return [
            'insumos' => $insumos,
            'valor_total' => $insumos->sum(fn($i) => $i->stock_actual * $i->precio_unitario),
            'stock_bajo' => $insumos->filter(fn($i) => $i->stock_actual <= $i->stock_minimo),
            'stock_critico' => $insumos->filter(fn($i) => $i->stock_actual == 0)
        ];
    }

    public function reporteProduccion(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $fechaInicio = $fechaInicio ?? now()->startOfMonth()->toDateString();
        $fechaFin = $fechaFin ?? now()->endOfMonth()->toDateString();
        
        $producciones = Produccion::with(['receta', 'responsable'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();
        
        return [
            'producciones' => $producciones,
            'total_producciones' => $producciones->sum('cantidad_producida'),
            'recetas_mas_producidas' => $producciones->groupBy('receta_id')
                ->map(fn($group) => [
                    'receta' => $group->first()->receta->nombre,
                    'cantidad' => $group->sum('cantidad_producida')
                ])
                ->sortByDesc('cantidad')
                ->take(10)
        ];
    }

    protected function getProductosMasVendidos(string $fechaInicio, string $fechaFin): array
    {
        return DB::table('detalle_venta')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->join('recetas', 'detalle_venta.receta_id', '=', 'recetas.id')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->select('recetas.nombre', DB::raw('SUM(detalle_venta.cantidad) as total_vendido'))
            ->groupBy('recetas.id', 'recetas.nombre')
            ->orderByDesc('total_vendido')
            ->take(10)
            ->get()
            ->toArray();
    }
}
