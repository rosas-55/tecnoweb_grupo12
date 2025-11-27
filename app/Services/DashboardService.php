<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Produccion;
use App\Models\Insumo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getWidgets(): array
    {
        return [
            'ventas_hoy' => [
                'label' => 'Ventas Hoy',
                'value' => Venta::whereDate('fecha', today())->sum('total') ?? 0,
                'icon' => '💰',
                'color' => 'blue'
            ],
            'ventas_mes' => [
                'label' => 'Ventas del Mes',
                'value' => Venta::whereMonth('fecha', now()->month)->sum('total') ?? 0,
                'icon' => '📊',
                'color' => 'green'
            ],
            'ventas_pendientes' => [
                'label' => 'Ventas Pendientes',
                'value' => Venta::where('estado', 0)->count(),
                'icon' => '⏳',
                'color' => 'yellow'
            ],
            'stock_bajo' => [
                'label' => 'Stock Bajo',
                'value' => Insumo::whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
                'icon' => '⚠️',
                'color' => 'red'
            ],
            'producciones_mes' => [
                'label' => 'Producciones del Mes',
                'value' => Produccion::whereMonth('fecha', now()->month)->count(),
                'icon' => '🏭',
                'color' => 'purple'
            ]
        ];
    }

    public function getGraficas(): array
    {
        return [
            'ventas_ultimos_7_dias' => $this->getVentasUltimos7Dias(),
            'ventas_por_tipo' => $this->getVentasPorTipo(),
            'productos_mas_vendidos' => $this->getProductosMasVendidos()
        ];
    }

    public function getPageVisits(): array
    {
        $allKeys = Cache::get('page_visits_keys', []);
        $visits = [];
        
        foreach ($allKeys as $key) {
            $path = str_replace('page_visits:', '', $key);
            $visits[$path] = Cache::get($key, 0);
        }
        
        arsort($visits);
        return $visits;
    }

    protected function getVentasUltimos7Dias(): array
    {
        $datos = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $total = Venta::whereDate('fecha', $fecha)->sum('total') ?? 0;
            
            $datos[] = [
                'fecha' => $fecha->format('d/m'),
                'total' => (float)$total
            ];
        }
        
        return $datos;
    }

    protected function getVentasPorTipo(): array
    {
        return [
            'contado' => Venta::where('tipo', 1)
                ->whereMonth('fecha', now()->month)
                ->sum('total') ?? 0,
            'credito' => Venta::where('tipo', 2)
                ->whereMonth('fecha', now()->month)
                ->sum('total') ?? 0
        ];
    }

    protected function getProductosMasVendidos(): array
    {
        return DB::table('detalle_venta')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->join('recetas', 'detalle_venta.receta_id', '=', 'recetas.id')
            ->whereMonth('ventas.fecha', now()->month)
            ->select('recetas.nombre', DB::raw('SUM(detalle_venta.cantidad) as total'))
            ->groupBy('recetas.id', 'recetas.nombre')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn($item) => [
                'nombre' => $item->nombre,
                'cantidad' => (int)$item->total
            ])
            ->toArray();
    }
}
