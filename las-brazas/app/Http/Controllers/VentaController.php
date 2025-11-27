<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Venta;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class VentaController extends Controller
{
    public function index(): Response
    {
        $ventas = Venta::with('user')->latest('id')->paginate(10);
        $users = User::orderBy('name')->get(['id','name']);
        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'tipo' => 'required|string|max:50',
            'total' => 'required|numeric|min:0',
            'estado' => 'required|boolean',
            'user_id' => 'required|exists:users,id',
        ]);

        Venta::create($data);
        return redirect()->route('ventas.index')->with('success','Venta creada');
    }

    public function update(Request $request, Venta $venta): RedirectResponse
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'tipo' => 'required|string|max:50',
            'total' => 'required|numeric|min:0',
            'estado' => 'required|boolean',
            'user_id' => 'required|exists:users,id',
        ]);

        $venta->update($data);
        return redirect()->route('ventas.index')->with('success','Venta actualizada');
    }

    public function destroy(Venta $venta): RedirectResponse
    {
        $venta->delete();
        return redirect()->route('ventas.index')->with('success','Venta eliminada');
    }
}


