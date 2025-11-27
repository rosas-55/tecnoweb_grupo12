<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReporteController extends Controller
{
    protected $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
    }

    public function index()
    {
        $estadisticas = $this->reporteService->getEstadisticasGenerales();
        
        return Inertia::render('Reportes/Index', [
            'estadisticas' => $estadisticas
        ]);
    }

    public function generar(Request $request)
    {
        $tipo = $request->input('tipo', 'ventas');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        switch ($tipo) {
            case 'ventas':
                return $this->ventas($request);
            case 'produccion':
                return $this->produccion($request);
            case 'inventario':
                return $this->inventario();
            default:
                return redirect()->route('reportes.index');
        }
    }

    public function ventas(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        $reporte = $this->reporteService->reporteVentas($fechaInicio, $fechaFin);
        
        return Inertia::render('Reportes/Ventas', [
            'reporte' => $reporte,
            'filtros' => compact('fechaInicio', 'fechaFin')
        ]);
    }

    public function inventario()
    {
        $reporte = $this->reporteService->reporteInventario();
        
        return Inertia::render('Reportes/Inventario', [
            'reporte' => $reporte
        ]);
    }

    public function produccion(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        $reporte = $this->reporteService->reporteProduccion($fechaInicio, $fechaFin);
        
        return Inertia::render('Reportes/Produccion', [
            'reporte' => $reporte,
            'filtros' => compact('fechaInicio', 'fechaFin')
        ]);
    }
}
