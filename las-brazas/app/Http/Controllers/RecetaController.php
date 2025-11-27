<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\Insumo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class RecetaController extends Controller
{
    public function index(): Response
    {
        $recetas = Receta::with('insumos')->orderBy('id', 'desc')->get();
        $insumos = Insumo::orderBy('nombre')->get(['id', 'nombre', 'unidad_medida']);

        return Inertia::render('Recetas/Index', [
            'recetas' => $recetas,
            'insumos' => $insumos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'tiempo_preparacion' => 'required|numeric|min:0',
            'estado' => 'required|boolean',
            'items' => 'array',
            'items.*.insumo_id' => 'required|exists:insumos,id',
            'items.*.cantidad' => 'required|numeric|min:0.0001',
        ]);

        $receta = Receta::create(Arr::except($data, 'items'));

        if (!empty($data['items'])) {
            $pivot = [];
            foreach ($data['items'] as $item) {
                $pivot[$item['insumo_id']] = ['cantidad' => $item['cantidad']];
            }
            $receta->insumos()->sync($pivot);
        }

        return redirect()->route('recetas.index')->with('success', 'Receta creada');
    }

    public function update(Request $request, Receta $receta): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'tiempo_preparacion' => 'required|numeric|min:0',
            'estado' => 'required|boolean',
            'items' => 'array',
            'items.*.insumo_id' => 'required|exists:insumos,id',
            'items.*.cantidad' => 'required|numeric|min:0.0001',
        ]);

        $receta->update(Arr::except($data, 'items'));

        $pivot = [];
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $pivot[$item['insumo_id']] = ['cantidad' => $item['cantidad']];
            }
        }
        $receta->insumos()->sync($pivot);

        return redirect()->route('recetas.index')->with('success', 'Receta actualizada');
    }

    public function destroy(Receta $receta): RedirectResponse
    {
        $receta->delete();
        return redirect()->route('recetas.index')->with('success', 'Receta eliminada');
    }
}


