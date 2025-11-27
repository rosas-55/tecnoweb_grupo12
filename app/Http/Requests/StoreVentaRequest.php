<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
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
            'cliente_id' => ['nullable', 'exists:users,id'],
            'fecha' => ['required', 'date'],
            'tipo' => ['required', 'integer', 'in:1,2'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.receta_id' => ['required', 'exists:recetas,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0']
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha de venta es obligatoria.',
            'tipo.required' => 'El tipo de pago es obligatorio.',
            'tipo.in' => 'El tipo debe ser 1 (Contado) o 2 (Crédito).',
            'detalles.required' => 'Debe agregar al menos un producto.',
            'detalles.*.receta_id.required' => 'Debe seleccionar un producto.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria.',
            'detalles.*.precio_unitario.required' => 'El precio es obligatorio.'
        ];
    }
}
