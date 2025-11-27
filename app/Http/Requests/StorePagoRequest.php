<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
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
            'venta_id' => ['required', 'exists:ventas,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodopago' => ['required', 'integer', 'in:1,2,3,4,5'],
            'fechapago' => ['required', 'date'],
            'estado' => ['required', 'integer', 'in:0,1']
        ];
    }

    public function messages(): array
    {
        return [
            'venta_id.required' => 'Debe seleccionar una venta.',
            'monto.required' => 'El monto es obligatorio.',
            'metodopago.required' => 'El método de pago es obligatorio.',
            'metodopago.in' => 'Método inválido: 1=Efectivo, 2=Tarjeta, 3=Transferencia, 4=Cheque, 5=QR Code.',
            'fechapago.required' => 'La fecha de pago es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'Estado inválido: 0=Pendiente, 1=Pagado.'
        ];
    }
}
