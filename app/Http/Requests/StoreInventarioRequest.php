<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Temporalmente deshabilitado
    }

    public function rules(): array
    {
        return [
            'insumo_id' => ['required', 'exists:insumos,id'],
            'tipo_movimiento' => ['required', 'in:ingreso,salida'],
            'cantidad' => ['required', 'numeric', 'min:0.01'],
            'fecha' => ['required', 'date'],
            'observacion' => ['nullable', 'string', 'max:500'],
            'metodo_inventario' => ['nullable', 'string', 'max:100']
        ];
    }

    public function messages(): array
    {
        return [
            'insumo_id.required' => 'Debe seleccionar un insumo.',
            'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'fecha.required' => 'La fecha es obligatoria.'
        ];
    }
}
