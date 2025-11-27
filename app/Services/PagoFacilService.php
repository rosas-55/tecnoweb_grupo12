<?php

namespace App\Services;

use App\Models\Pago;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagoFacilService
{
    private string $baseUrl;
    private string $tokenService;
    private string $tokenSecret;
    private ?string $accessToken = null;
    private string $paymentPrefix;

    public function __construct()
    {
        $this->baseUrl = config('services.pagofacil.base_url', 'https://masterqr.pagofacil.com.bo/api/services/v2');
        $this->tokenService = config('services.pagofacil.token_service');
        $this->tokenSecret = config('services.pagofacil.token_secret');
        $this->paymentPrefix = config('services.pagofacil.payment_prefix', 'grupo12sc');

        // Verificar que los tokens estén configurados
        if (empty($this->tokenService) || empty($this->tokenSecret)) {
            Log::warning('PagoFácil: Tokens no configurados en .env', [
                'has_token_service' => !empty($this->tokenService),
                'has_token_secret' => !empty($this->tokenSecret)
            ]);
        }
    }

    /**
     * Obtener token de acceso (con caché de 1 hora)
     */
    public function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            // Configurar opciones de cURL para ignorar SSL en desarrollo
            $response = Http::withOptions([
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ]
            ])
            ->withHeaders([
                'tcTokenService' => $this->tokenService,
                'tcTokenSecret' => $this->tokenSecret,
            ])
            ->post("{$this->baseUrl}/login");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data']['token'])) {
                    $this->accessToken = $data['data']['token'];
                    return $this->accessToken;
                }
            }

            Log::error('Error al obtener token de PagoFácil', [
                'status' => $response->status(),
                'response' => $response->body(),
                'base_url' => $this->baseUrl,
                'has_token_service' => !empty($this->tokenService),
                'has_token_secret' => !empty($this->tokenSecret)
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Excepción al obtener token de PagoFácil', [
                'message' => $e->getMessage(),
                'base_url' => $this->baseUrl,
                'has_token_service' => !empty($this->tokenService),
                'has_token_secret' => !empty($this->tokenSecret),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Generar código QR para pago
     */
    public function generateQR(array $paymentData): array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return [
                'success' => false,
                'message' => 'No se pudo obtener el token de autenticación'
            ];
        }

        try {
            $response = Http::withOptions([
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ],
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ])
            ->post("{$this->baseUrl}/generate-qr", $paymentData);

            if ($response->successful()) {
                $data = $response->json();
                $responseData = $data['data'] ?? $data;

                Log::info('QR generado exitosamente por PagoFácil', [
                    'response_structure' => array_keys($responseData),
                    'has_qrCode' => isset($responseData['qrCode']),
                    'has_qr_code' => isset($responseData['qr_code']),
                    'has_transactionId' => isset($responseData['transactionId']),
                    'has_transaction_id' => isset($responseData['transaction_id'])
                ]);

                return [
                    'success' => true,
                    'data' => $responseData
                ];
            }

            $errorBody = $response->json();
            Log::error('Error al generar QR de PagoFácil', [
                'status' => $response->status(),
                'response' => $errorBody,
                'request' => $paymentData
            ]);

            return [
                'success' => false,
                'message' => $errorBody['message'] ?? 'Error al generar el código QR',
                'errors' => $errorBody['errors'] ?? []
            ];
        } catch (\Exception $e) {
            Log::error('Excepción al generar QR de PagoFácil', [
                'message' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Consultar estado de una transacción
     */
    public function queryTransaction(string $transactionId): array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return [
                'success' => false,
                'message' => 'No se pudo obtener el token de autenticación'
            ];
        }

        try {
            $response = Http::withOptions([
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ],
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ])
            ->get("{$this->baseUrl}/query-transaction", [
                'transactionId' => $transactionId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data['data'] ?? $data
                ];
            }

            $errorData = $response->json();
            Log::error('Error al consultar transacción de PagoFácil', [
                'status' => $response->status(),
                'response' => $errorData,
                'transactionId' => $transactionId
            ]);

            return [
                'success' => false,
                'message' => $errorData['message'] ?? 'Error al consultar la transacción'
            ];
        } catch (\Exception $e) {
            Log::error('Excepción al consultar transacción de PagoFácil', [
                'message' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar número de pago (paymentNumber) con formato consistente
     */
    public function buildPaymentNumber(Pago $pago): string
    {
        $suffix = str_pad((string)$pago->id, 5, '0', STR_PAD_LEFT);
        return "{$this->paymentPrefix}_{$suffix}";
    }

    /**
     * Preparar datos para generar QR basado en una venta
     */
    public function preparePaymentData($venta, $cliente, float $monto, string $paymentNumber): array
    {
        $orderDetail = [];
        $serial = 1;

        // Obtener detalles de la venta
        foreach ($venta->detalleVentas as $detalle) {
            $orderDetail[] = [
                'serial' => $serial++,
                'product' => $detalle->receta->nombre ?? 'Producto',
                'quantity' => (int)$detalle->cantidad,
                'price' => (float)$detalle->precio_unitario,
                'discount' => 0,
                'total' => (float)$detalle->subtotal
            ];
        }

        // Construir URL completa del callback (puede configurarse vía .env)
        $configuredCallback = config('services.pagofacil.callback_url');
        $callbackUrl = $configuredCallback ?: route('pagos.callback', [], true);

        $clientCode = $cliente && $cliente->id
            ? "cli_{$cliente->id}"
            : config('services.pagofacil.client_code', '11001');

        return [
            'paymentMethod' => 4, // QR Code
            'clientName' => $cliente->name ?? 'Cliente',
            'documentType' => 1, // CI
            'documentId' => $cliente->cedula ?? '0000000',
            'phoneNumber' => $cliente->celular ?? '0000000',
            'email' => $cliente->email ?? '',
            'paymentNumber' => $paymentNumber, // Este será el PedidoID en el callback
            'amount' => $monto,
            'currency' => 2, // BOB (Bolivianos)
            'clientCode' => $clientCode,
            'callbackUrl' => $callbackUrl, // URL del callback para recibir notificaciones
            'orderDetail' => $orderDetail
        ];
    }
}

