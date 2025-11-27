<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInsumoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Temporalmente deshabilitado
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'unidad_medida' => ['required', 'string', 'max:50'],
            'costo_unitario' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['required', 'numeric', 'min:0'],
            'stock_actual' => ['required', 'numeric', 'min:0']
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del insumo es obligatorio.',
            'unidad_medida.required' => 'La unidad de medida es obligatoria.',
            'costo_unitario.required' => 'El costo unitario es obligatorio.',
            'stock_minimo.required' => 'El stock mínimo es obligatorio.',
            'stock_actual.required' => 'El stock actual es obligatorio.'
        ];
    }
}
