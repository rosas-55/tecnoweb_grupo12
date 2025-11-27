<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProduccionRequest;
use App\Models\Produccion;
use App\Models\Receta;
use App\Models\User;
use App\Services\ProduccionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProduccionController extends Controller
{
    protected $produccionService;

    public function __construct(ProduccionService $produccionService)
    {
        $this->produccionService = $produccionService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $producciones = $this->produccionService->paginate($search);

        return Inertia::render('Produccion/Index', [
            'producciones' => $producciones,
            'search' => $search
        ]);
    }

    public function create()
    {
        $recetas = Receta::with('insumos')->get();

        return Inertia::render('Produccion/Create', [
            'recetas' => $recetas,
            'usuario' => Auth::user()
        ]);
    }

    public function store(StoreProduccionRequest $request)
    {
        try {
            $data = $request->validated();
            $data['responsable_id'] = Auth::id();

            $this->produccionService->create($data);

            return redirect()->route('produccion.index')
                ->with('success', 'Producción registrada exitosamente.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['general' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Produccion $produccion)
    {
        $produccion->load(['receta', 'responsable']);

        return Inertia::render('Produccion/Show', [
            'produccion' => $produccion
        ]);
    }

    public function edit(Produccion $produccion)
    {
        $recetas = Receta::with('insumos')->get();

        return Inertia::render('Produccion/Edit', [
            'produccion' => $produccion,
            'recetas' => $recetas
        ]);
    }

    public function update(StoreProduccionRequest $request, Produccion $produccion)
    {
        $data = $request->validated();
        $produccion->update($data);

        return redirect()->route('produccion.index')
            ->with('success', 'Producción actualizada exitosamente.');
    }

    public function destroy(Produccion $produccion)
    {
        $produccion->delete();

        return redirect()->route('produccion.index')
            ->with('success', 'Producción eliminada exitosamente.');
    }
}
