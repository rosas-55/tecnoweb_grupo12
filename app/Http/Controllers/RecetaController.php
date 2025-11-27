<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecetaRequest;
use App\Http\Requests\UpdateRecetaRequest;
use App\Models\Insumo;
use App\Models\Receta;
use App\Services\RecetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RecetaController extends Controller
{
    protected $recetaService;

    public function __construct(RecetaService $recetaService)
    {
        $this->recetaService = $recetaService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $recetas = $this->recetaService->paginate($search);
        
        return Inertia::render('Recetas/Index', [
            'recetas' => $recetas,
            'search' => $search
        ]);
    }

    public function create()
    {
        $insumos = Insumo::all();
        
        return Inertia::render('Recetas/Create', [
            'insumos' => $insumos
        ]);
    }

    public function store(StoreRecetaRequest $request)
    {
        $receta = $this->recetaService->create($request->validated());
        
        return redirect()->route('recetas.index')
            ->with('success', 'Receta creada exitosamente.');
    }

    public function show(Receta $receta)
    {
        $receta->load('insumos');
        
        return Inertia::render('Recetas/Show', [
            'receta' => $receta
        ]);
    }

    public function edit(Receta $receta)
    {
        $insumos = Insumo::all();
        $receta->load('insumos');
        
        return Inertia::render('Recetas/Edit', [
            'receta' => $receta,
            'insumos' => $insumos
        ]);
    }

    public function update(UpdateRecetaRequest $request, Receta $receta)
    {
        $this->recetaService->update($receta, $request->validated());
        
        return redirect()->route('recetas.index')
            ->with('success', 'Receta actualizada exitosamente.');
    }

    public function destroy(Receta $receta)
    {
        $this->recetaService->delete($receta);
        
        return redirect()->route('recetas.index')
            ->with('success', 'Receta eliminada exitosamente.');
    }
}
