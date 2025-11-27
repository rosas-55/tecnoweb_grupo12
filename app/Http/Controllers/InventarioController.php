<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventarioRequest;
use App\Models\Insumo;
use App\Models\Inventario;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventarioController extends Controller
{
    protected $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $tipo = $request->input('tipo');
        
        $inventarios = $this->inventarioService->paginate($search, $tipo);
        
        return Inertia::render('Inventarios/Index', [
            'inventarios' => $inventarios,
            'search' => $search,
            'tipo' => $tipo
        ]);
    }

    public function create()
    {
        $insumos = Insumo::all();
        
        return Inertia::render('Inventarios/Create', [
            'insumos' => $insumos
        ]);
    }

    public function store(StoreInventarioRequest $request)
    {
        $inventario = $this->inventarioService->create($request->validated());
        
        return redirect()->route('inventarios.index')
            ->with('success', 'Movimiento de inventario registrado exitosamente.');
    }

    public function edit(Inventario $inventario)
    {
        $insumos = Insumo::all();
        
        return Inertia::render('Inventarios/Edit', [
            'inventario' => $inventario,
            'insumos' => $insumos
        ]);
    }

    public function update(StoreInventarioRequest $request, Inventario $inventario)
    {
        $inventario->update($request->validated());
        
        return redirect()->route('inventarios.index')
            ->with('success', 'Movimiento de inventario actualizado exitosamente.');
    }

    public function destroy(Inventario $inventario)
    {
        $inventario->delete();
        
        return redirect()->route('inventarios.index')
            ->with('success', 'Movimiento eliminado exitosamente.');
    }

    public function kardex(Insumo $insumo)
    {
        $movimientos = $this->inventarioService->getKardex($insumo);
        
        return Inertia::render('Inventarios/Kardex', [
            'insumo' => $insumo,
            'movimientos' => $movimientos
        ]);
    }
}
