<?php

namespace App\Http\Controllers;

use App\Helpers\UnidadConverter;
use App\Models\Inventario;
use App\Models\Insumo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InventarioController extends Controller
{
    public function index(): Response
    {
        $movimientos = Inventario::with('insumo')->latest('fecha')->paginate(10);
        $insumos = Insumo::orderBy('nombre')->get(['id', 'nombre', 'unidad_medida']);

        return Inertia::render('Inventario/Index', [
            'movimientos' => $movimientos,
            'insumos' => $insumos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'insumo_id' => 'required|exists:insumos,id',
            'tipo_movimiento' => 'required|string|max:20', // Entrada|Salida
            'cantidad' => 'required|numeric|min:0.0001',
            'unidad_movimiento' => 'nullable|string|max:20', // Unidad del movimiento (opcional, si no se envía usa la del insumo)
            'fecha' => 'required|date',
            'metodo_inventario' => 'nullable|string|max:100',
            'observacion' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            // Obtener el insumo para conocer su unidad base
            $insumo = Insumo::lockForUpdate()->findOrFail($data['insumo_id']);
            $unidadBase = $insumo->unidad_medida;

            // Si se especificó una unidad diferente, convertir
            $cantidadConvertida = $data['cantidad'];
            $unidadUsada = $unidadBase;

            if (!empty($data['unidad_movimiento']) && $data['unidad_movimiento'] !== $unidadBase) {
                try {
                    $cantidadConvertida = UnidadConverter::convertir(
                        $data['cantidad'],
                        $data['unidad_movimiento'],
                        $unidadBase
                    );
                    $unidadUsada = $data['unidad_movimiento'];
                    // Agregar info de conversión a la observación
                    if (empty($data['observacion'])) {
                        $data['observacion'] = "Movimiento en {$unidadUsada}, convertido a {$unidadBase}";
                    } else {
                        $data['observacion'] .= " (Movimiento en {$unidadUsada}, convertido a {$unidadBase})";
                    }
                } catch (\InvalidArgumentException $e) {
                    throw ValidationException::withMessages([
                        'unidad_movimiento' => $e->getMessage(),
                    ]);
                }
            }

            // Guardar el movimiento con la cantidad convertida
            $data['cantidad'] = $cantidadConvertida;
            $mov = Inventario::create($data);

            // Actualizar stock del insumo
            $factor = $data['tipo_movimiento'] === 'Salida' ? -1 : 1;
            $nuevoStock = $insumo->stock_actual + ($factor * $cantidadConvertida);

            if ($nuevoStock < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad deja el stock en negativo.',
                ]);
            }

            $insumo->stock_actual = $nuevoStock;
            $insumo->save();
        });

        return redirect()->route('inventario.index')->with('success', 'Movimiento registrado');
    }

    public function update(Request $request, Inventario $inventario): RedirectResponse
    {
        $data = $request->validate([
            'insumo_id' => 'required|exists:insumos,id',
            'tipo_movimiento' => 'required|string|max:20',
            'cantidad' => 'required|numeric|min:0.0001',
            'unidad_movimiento' => 'nullable|string|max:20',
            'fecha' => 'required|date',
            'metodo_inventario' => 'nullable|string|max:100',
            'observacion' => 'nullable|string',
        ]);

        DB::transaction(function () use ($inventario, $data) {
            // Revertir efecto anterior
            $insumoAnterior = Insumo::lockForUpdate()->findOrFail($inventario->insumo_id);
            $factorAnterior = $inventario->tipo_movimiento === 'Salida' ? -1 : 1;
            $insumoAnterior->stock_actual -= $factorAnterior * $inventario->cantidad;
            $insumoAnterior->save();

            // Obtener el insumo nuevo para conocer su unidad base
            $insumoNuevo = Insumo::lockForUpdate()->findOrFail($data['insumo_id']);
            $unidadBase = $insumoNuevo->unidad_medida;

            // Si se especificó una unidad diferente, convertir
            $cantidadConvertida = $data['cantidad'];

            if (!empty($data['unidad_movimiento']) && $data['unidad_movimiento'] !== $unidadBase) {
                try {
                    $cantidadConvertida = UnidadConverter::convertir(
                        $data['cantidad'],
                        $data['unidad_movimiento'],
                        $unidadBase
                    );
                    $unidadUsada = $data['unidad_movimiento'];
                    // Agregar info de conversión a la observación
                    if (empty($data['observacion'])) {
                        $data['observacion'] = "Movimiento en {$unidadUsada}, convertido a {$unidadBase}";
                    } else {
                        $data['observacion'] .= " (Movimiento en {$unidadUsada}, convertido a {$unidadBase})";
                    }
                } catch (\InvalidArgumentException $e) {
                    throw ValidationException::withMessages([
                        'unidad_movimiento' => $e->getMessage(),
                    ]);
                }
            }

            // Guardar el movimiento con la cantidad convertida
            $data['cantidad'] = $cantidadConvertida;
            $inventario->update($data);

            // Aplicar nuevo efecto
            $factorNuevo = $data['tipo_movimiento'] === 'Salida' ? -1 : 1;
            $nuevoStock = $insumoNuevo->stock_actual + ($factorNuevo * $cantidadConvertida);

            if ($nuevoStock < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad deja el stock en negativo.',
                ]);
            }

            $insumoNuevo->stock_actual = $nuevoStock;
            $insumoNuevo->save();
        });

        return redirect()->route('inventario.index')->with('success', 'Movimiento actualizado');
    }

    public function destroy(Inventario $inventario): RedirectResponse
    {
        DB::transaction(function () use ($inventario) {
            $insumo = Insumo::lockForUpdate()->findOrFail($inventario->insumo_id);
            $factor = $inventario->tipo_movimiento === 'Salida' ? -1 : 1;
            $nuevoStock = $insumo->stock_actual - ($factor * $inventario->cantidad);

            if ($nuevoStock < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => 'No se puede eliminar el movimiento porque dejaría el stock en negativo.',
                ]);
            }

            $insumo->stock_actual = $nuevoStock;
            $insumo->save();

            $inventario->delete();
        });

        return redirect()->route('inventario.index')->with('success', 'Movimiento eliminado');
    }
}


