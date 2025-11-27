<?php

namespace App\Http\Controllers;

use App\Models\Produccion;
use App\Models\Receta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProduccionController extends Controller
{
    public function index(): Response
    {
        $producciones = Produccion::with('receta.insumos')->latest('fecha')->paginate(10);
        $recetas = Receta::with('insumos')->orderBy('nombre')->get();

        return Inertia::render('Produccion/Index', [
            'producciones' => $producciones,
            'recetas' => $recetas,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'receta_id' => 'required|exists:recetas,id',
            'cantidad_producida' => 'required|numeric|min:1',
            'fecha' => 'required|date',
        ]);

        DB::transaction(function () use ($data) {
            // Registrar producción
            Produccion::create($data);
            // Nota: el descuento de insumos según receta se implementará en un siguiente paso,
            // recorriendo receta->insumos y restando 'cantidad' * 'cantidad_producida' en inventario.
        });

        return redirect()->route('produccion.index')->with('success', 'Producción registrada');
    }

    public function update(Request $request, Produccion $produccion): RedirectResponse
    {
        $data = $request->validate([
            'receta_id' => 'required|exists:recetas,id',
            'cantidad_producida' => 'required|numeric|min:1',
            'fecha' => 'required|date',
        ]);

        DB::transaction(function () use ($produccion, $data) {
            $produccion->update($data);
            // Ajuste de inventario quedará en la fase con descuento real
        });

        return redirect()->route('produccion.index')->with('success', 'Producción actualizada');
    }

    public function destroy(Produccion $produccion): RedirectResponse
    {
        $produccion->delete();
        return redirect()->route('produccion.index')->with('success', 'Producción eliminada');
    }
}


