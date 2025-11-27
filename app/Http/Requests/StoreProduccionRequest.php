<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProduccionRequest extends FormRequest
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
            'receta_id' => ['required', 'exists:recetas,id'],
            'cantidad_producida' => ['required', 'integer', 'min:1'],
            'fecha' => ['required', 'date']
        ];
    }

    public function messages(): array
    {
        return [
            'receta_id.required' => 'Debe seleccionar una receta.',
            'cantidad_producida.required' => 'La cantidad es obligatoria.',
            'fecha.required' => 'La fecha de producción es obligatoria.'
        ];
    }
}
