<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecetaRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'tiempo_preparacion' => ['nullable', 'integer', 'min:0'],
            'insumos' => ['required', 'array', 'min:1'],
            'insumos.*.insumo_id' => ['required', 'exists:insumos,id'],
            'insumos.*.cantidad' => ['required', 'numeric', 'min:0.01']
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la receta es obligatorio.',
            'insumos.required' => 'Debe agregar al menos un insumo.',
            'insumos.*.insumo_id.required' => 'Debe seleccionar un insumo.',
            'insumos.*.cantidad.required' => 'La cantidad es obligatoria.'
        ];
    }
}
