<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Models\Pago;
use App\Models\Venta;
use App\Services\PagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PagoController extends Controller
{
    protected $pagoService;

    public function __construct(PagoService $pagoService)
    {
        $this->pagoService = $pagoService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $pagos = $this->pagoService->paginate($search);

        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'search' => $search
        ]);
    }

    public function create()
    {
        $ventasPendientes = Venta::where('estado', 0) // solo pendientes (contado o crédito)
            ->with(['cliente', 'pagos'])
            ->get()
            ->map(function ($venta) {
                $totalPagado = $venta->pagos
                    ->where('estado', 1)
                    ->sum('monto');

                $saldoPendiente = max(($venta->total ?? 0) - $totalPagado, 0);

                $venta->setAttribute('total_pagado', $totalPagado);
                $venta->setAttribute('saldo_pendiente', $saldoPendiente);

                return $venta;
            });

        return Inertia::render('Pagos/Create', [
            'ventas' => $ventasPendientes
        ]);
    }

    public function store(StorePagoRequest $request)
    {
        $pago = $this->pagoService->create($request->validated());

        // Si es pago QR, redirigir a la página de visualización del QR
        if ((int)$request->metodopago === 5) {
            return redirect()->route('pagos.show', $pago)
                ->with('success', 'Código QR generado exitosamente. Escanea el código para realizar el pago.');
        }

        return redirect()->route('pagos.index')
            ->with('success', 'Pago registrado exitosamente.');
    }

    public function show(Pago $pago)
    {
        $pago->load('venta.cliente');

        // Asegurar que qr_data esté disponible
        $qrData = $pago->qr_data;

        // Si es pago QR pero no tiene datos, intentar regenerar
        if ($pago->metodopago == 5 && !$qrData) {
            Log::warning('Pago QR sin datos de QR', ['pago_id' => $pago->id]);
        }

        // Preparar datos del pago incluyendo qr_data explícitamente
        $pagoData = $pago->toArray();
        $pagoData['qr_data'] = $qrData; // Asegurar que qr_data esté presente

        return Inertia::render('Pagos/Show', [
            'pago' => $pagoData
        ]);
    }

    /**
     * Callback para recibir notificaciones de PagoFácil
     * Recibe: { "PedidoID", "Fecha", "Hora", "MetodoPago", "Estado" }
     * Responde: { "error": 0, "status": 1, "message": "...", "values": true }
     */
    public function callback(Request $request)
    {
        Log::info('Callback recibido de PagoFácil', $request->all());

        // Obtener datos según la estructura de la documentación
        $pedidoID = $request->input('PedidoID');
        $fecha = $request->input('Fecha');
        $hora = $request->input('Hora');
        $metodoPago = $request->input('MetodoPago');
        $estado = $request->input('Estado');

        if (!$pedidoID) {
            Log::error('Callback sin PedidoID', $request->all());
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'PedidoID requerido',
                'values' => false
            ], 400);
        }

        // Buscar el pago por paymentNumber (PedidoID)
        $pago = null;
        $pagos = Pago::where('metodopago', 5)->get();

        foreach ($pagos as $p) {
            $qrData = $p->qr_data;
            if ($qrData && isset($qrData['payment_number']) && $qrData['payment_number'] === $pedidoID) {
                $pago = $p;
                break;
            }
        }

        if (!$pago) {
            Log::warning('Pago no encontrado para PedidoID', ['PedidoID' => $pedidoID]);
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'Pago no encontrado',
                'values' => false
            ], 404);
        }

        // Actualizar estado del pago con los datos recibidos
        $qrData = $pago->qr_data ?? [];
        $qrData['status'] = $estado;
        $qrData['fecha_pago'] = $fecha;
        $qrData['hora_pago'] = $hora;
        $qrData['metodo_pago_callback'] = $metodoPago;
        $qrData['callback_received_at'] = now()->toDateTimeString();
        $pago->setQrData($qrData);

        // Si el pago fue confirmado (Estado puede ser "Pagado", "Aprobado", "paid", "approved", etc.)
        $estadoLower = strtolower($estado ?? '');
        if (in_array($estadoLower, ['pagado', 'aprobado', 'paid', 'approved', 'completado', 'completed'])) {
            $pago->update(['estado' => 1]);

            // Actualizar fecha de pago si se recibió
            if ($fecha) {
                try {
                    $fechaPago = \Carbon\Carbon::parse($fecha);
                    $pago->update(['fechapago' => $fechaPago->format('Y-m-d H:i:s')]);
                } catch (\Exception $e) {
                    Log::warning('Error al parsear fecha de pago', ['fecha' => $fecha, 'error' => $e->getMessage()]);
                }
            }

            // Verificar si la venta está completamente pagada
            $venta = $pago->venta;
            $totalPagado = $venta->pagos()->where('estado', 1)->sum('monto');

            if ($totalPagado >= $venta->total) {
                $venta->update(['estado' => 1]);
            }
        }

        // Respuesta según la documentación
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message' => 'Notificación recibida y procesada exitosamente',
            'values' => true
        ], 200);
    }

    /**
     * Consultar estado de un pago QR
     */
    public function checkStatus(Pago $pago)
    {
        $result = $this->pagoService->checkQRStatus($pago);

        return response()->json($result);
    }
}
