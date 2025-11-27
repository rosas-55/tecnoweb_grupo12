<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Inventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsumoController extends Controller
{
    public function index(): Response
    {
        $insumos = Insumo::orderBy('nombre')->paginate(10);
        return Inertia::render('Insumos/Index', [
            'insumos' => $insumos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo' => 'required|numeric|min:0',
            'costo_unitario' => 'required|numeric|min:0',
            'estado' => 'required|boolean',
        ]);

        // El stock_actual siempre inicia en 0 para insumos nuevos
        // Solo se actualiza mediante movimientos de inventario
        $data['stock_actual'] = 0;

        Insumo::create($data);

        return redirect()->route('insumos.index')
            ->with('success', 'Insumo creado');
    }

    public function update(Request $request, Insumo $insumo): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo' => 'required|numeric|min:0',
            'costo_unitario' => 'required|numeric|min:0',
            'estado' => 'required|boolean',
        ]);

        // El stock_actual no se puede editar manualmente
        // Solo se actualiza mediante movimientos de inventario
        // Mantenemos el valor actual del insumo

        $insumo->update($data);

        return redirect()->route('insumos.index')
            ->with('success', 'Insumo actualizado');
    }

    public function destroy(Insumo $insumo): RedirectResponse
    {
        // Si el insumo tiene movimientos de inventario o está asociado a recetas,
        // no se elimina físicamente, sólo se desactiva.
        $tieneMovimientos = $insumo->inventarios()->exists();
        $tieneRecetas = $insumo->recetas()->exists();

        if ($tieneMovimientos || $tieneRecetas) {
            $insumo->estado = false;
            $insumo->save();

            return redirect()
                ->route('insumos.index')
                ->with('success', 'Insumo desactivado (tiene movimientos o recetas asociadas)');
        }

        $insumo->delete();

        return redirect()
            ->route('insumos.index')
            ->with('success', 'Insumo eliminado');
    }
}


