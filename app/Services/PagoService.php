<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PagoService
{
    protected PagoFacilService $pagoFacilService;

    public function __construct(PagoFacilService $pagoFacilService)
    {
        $this->pagoFacilService = $pagoFacilService;
    }

    public function paginate(?string $search = null, int $perPage = 15)
    {
        $query = Pago::with('venta.cliente');

        if ($search) {
            $query->whereHas('venta.cliente', function ($clienteQuery) use ($search) {
                $clienteQuery->where('name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function create(array $data): Pago
    {
        return DB::transaction(function () use ($data) {
            $venta = Venta::with('cliente', 'detalleVentas.receta')->find($data['venta_id']);

            // Si es pago QR, el estado debe ser pendiente
            $estado = (int)$data['metodopago'] === 5 ? 0 : (int)($data['estado'] ?? 0);

            // Crear pago
            $pago = Pago::create([
                'venta_id' => $data['venta_id'],
                'monto' => $data['monto'],
                'metodopago' => (int)$data['metodopago'],
                'fechapago' => $data['fechapago'] ?? now(),
                'estado' => $estado
            ]);

            // Si el método de pago es QR (5), generar código QR
            if ((int)$data['metodopago'] === 5) {
                $this->generateQRForPayment($pago, $venta);
            }

            // Actualizar estado de venta
            $totalPagado = $venta->pagos()->sum('monto');

            if ($totalPagado >= $venta->total) {
                $venta->update(['estado' => 1]);
            }

            return $pago;
        });
    }

    /**
     * Generar código QR para un pago
     */
    protected function generateQRForPayment(Pago $pago, Venta $venta): void
    {
        try {
            $paymentNumber = $this->pagoFacilService->buildPaymentNumber($pago);
            $paymentData = $this->pagoFacilService->preparePaymentData(
                $venta,
                $venta->cliente,
                (float)$pago->monto,
                $paymentNumber
            );

            $result = $this->pagoFacilService->generateQR($paymentData);

            if ($result['success']) {
                $data = $result['data'] ?? [];
                // PagoFácil puede retornar qrCode o qr_code, transactionId o transaction_id
                $qrData = [
                    'qr_code' => $data['qrCode'] ?? $data['qr_code'] ?? $data['qr'] ?? null,
                    'transaction_id' => $data['transactionId'] ?? $data['transaction_id'] ?? $data['id'] ?? null,
                    'payment_number' => $paymentNumber,
                    'status' => 'pending',
                    'generated_at' => now()->toDateTimeString(),
                    'raw_response' => $data // Guardar respuesta completa para debugging
                ];

                Log::info('QR generado exitosamente', [
                    'pago_id' => $pago->id,
                    'payment_number' => $paymentNumber,
                    'has_qr_code' => !empty($qrData['qr_code']),
                    'transaction_id' => $qrData['transaction_id']
                ]);

                $pago->setQrData($qrData);
            } else {
                Log::error('Error al generar QR para pago', [
                    'pago_id' => $pago->id,
                    'payment_number' => $paymentNumber,
                    'error' => $result['message'] ?? 'Error desconocido',
                    'result' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Excepción al generar QR para pago', [
                'pago_id' => $pago->id,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Consultar estado de un pago QR
     */
    public function checkQRStatus(Pago $pago): array
    {
        $qrData = $pago->qr_data;

        if (!$qrData || !isset($qrData['transaction_id'])) {
            return [
                'success' => false,
                'message' => 'Este pago no tiene código QR asociado'
            ];
        }

        $result = $this->pagoFacilService->queryTransaction($qrData['transaction_id']);

        if ($result['success'] && isset($result['data'])) {
            // Actualizar estado en caché
            $qrData['status'] = $result['data']['status'] ?? 'unknown';
            $qrData['last_check'] = now()->toDateTimeString();
            $pago->setQrData($qrData);

            // Si el pago fue confirmado, actualizar estado del pago
            if (isset($result['data']['status']) && $result['data']['status'] === 'paid') {
                $pago->update(['estado' => 1]);
            }
        }

        return $result;
    }
}
