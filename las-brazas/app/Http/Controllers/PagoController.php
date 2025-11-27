<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Venta;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PagoController extends Controller
{
    public function index(): Response
    {
        $pagos = Pago::with('venta')->latest('fechapago')->paginate(10);
        $ventas = Venta::latest('id')->get(['id','total']);
        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'ventas' => $ventas,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fechapago' => 'required|date',
            'venta_id' => 'required|exists:ventas,id',
            'metodopago' => 'required|string|max:50',
            'monto' => 'required|numeric|min:0',
            'estado' => 'required|boolean',
        ]);
        Pago::create($data);
        return redirect()->route('pagos.index')->with('success','Pago registrado');
    }

    public function update(Request $request, Pago $pago): RedirectResponse
    {
        $data = $request->validate([
            'fechapago' => 'required|date',
            'venta_id' => 'required|exists:ventas,id',
            'metodopago' => 'required|string|max:50',
            'monto' => 'required|numeric|min:0',
            'estado' => 'required|boolean',
        ]);
        $pago->update($data);
        return redirect()->route('pagos.index')->with('success','Pago actualizado');
    }

    public function destroy(Pago $pago): RedirectResponse
    {
        $pago->delete();
        return redirect()->route('pagos.index')->with('success','Pago eliminado');
    }
}


