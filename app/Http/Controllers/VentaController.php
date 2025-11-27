<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Receta;
use App\Models\User;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentaController extends Controller
{
    protected $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $tipo = $request->input('tipo');

        $ventas = $this->ventaService->paginate($search, $tipo);

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'search' => $search,
            'tipo' => $tipo
        ]);
    }

    public function create()
    {
        $recetas = Receta::with('insumos')->get();
        $clientes = User::whereHas('roles', function ($query) {
            $query->whereRaw('LOWER(TRIM(nombre)) IN (?, ?)', ['clientes', 'cliente']);
        })->get();

        return Inertia::render('Ventas/Create', [
            'recetas' => $recetas,
            'clientes' => $clientes
        ]);
    }

    public function store(StoreVentaRequest $request)
    {
        $venta = $this->ventaService->create($request->validated());

        return redirect()->route('ventas.index')
            ->with('success', 'Venta registrada exitosamente.');
    }

    public function show(Venta $venta)
    {
        $venta->load(['detalleVentas.receta', 'cliente', 'usuario', 'pagos']);

        return Inertia::render('Ventas/Show', [
            'venta' => $venta
        ]);
    }

    public function edit(Venta $venta)
    {
        $venta->load(['detalleVentas.receta.insumos']);
        $recetas = Receta::with('insumos')->get();
        $clientes = User::whereHas('roles', function ($query) {
            $query->whereRaw('LOWER(TRIM(nombre)) IN (?, ?)', ['clientes', 'cliente']);
        })->get();

        return Inertia::render('Ventas/Edit', [
            'venta' => $venta,
            'recetas' => $recetas,
            'clientes' => $clientes
        ]);
    }

    public function update(StoreVentaRequest $request, Venta $venta)
    {
        $this->ventaService->update($venta, $request->validated());

        return redirect()->route('ventas.index')
            ->with('success', 'Venta actualizada exitosamente.');
    }

    public function destroy(Venta $venta)
    {
        $venta->detalleVentas()->delete();
        $venta->delete();

        return redirect()->route('ventas.index')
            ->with('success', 'Venta eliminada exitosamente.');
    }
}
