<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInsumoRequest;
use App\Http\Requests\UpdateInsumoRequest;
use App\Models\Insumo;
use App\Services\InsumoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InsumoController extends Controller
{
    protected $insumoService;

    public function __construct(InsumoService $insumoService)
    {
        $this->insumoService = $insumoService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $insumos = $this->insumoService->paginate($search);
        
        return Inertia::render('Insumos/Index', [
            'insumos' => $insumos,
            'search' => $search
        ]);
    }

    public function create()
    {
        return Inertia::render('Insumos/Create');
    }

    public function store(StoreInsumoRequest $request)
    {
        $insumo = $this->insumoService->create($request->validated());
        
        return redirect()->route('insumos.index')
            ->with('success', 'Insumo creado exitosamente.');
    }

    public function edit(Insumo $insumo)
    {
        return Inertia::render('Insumos/Edit', [
            'insumo' => $insumo
        ]);
    }

    public function update(UpdateInsumoRequest $request, Insumo $insumo)
    {
        $this->insumoService->update($insumo, $request->validated());
        
        return redirect()->route('insumos.index')
            ->with('success', 'Insumo actualizado exitosamente.');
    }

    public function destroy(Insumo $insumo)
    {
        $this->insumoService->delete($insumo);
        
        return redirect()->route('insumos.index')
            ->with('success', 'Insumo eliminado exitosamente.');
    }
}
