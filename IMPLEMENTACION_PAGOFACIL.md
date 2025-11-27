# Implementación de Pagos Reales con PagoFácil

## 📋 Resumen de lo Implementado

Se implementó la integración completa con PagoFácil para realizar pagos reales mediante códigos QR. El sistema ahora puede:
1. Generar códigos QR para pagos
2. Recibir notificaciones automáticas cuando un cliente paga
3. Actualizar automáticamente el estado de los pagos y ventas

---

## 🔧 Cambios Realizados

### 1. **Configuración en `.env`**
Se agregaron las credenciales de PagoFácil:
```env
PAGOFACIL_BASE_URL=https://masterqr.pagofacil.com.bo/api/services/v2
PAGOFACIL_TOKEN_SERVICE=51247fae280c20410824977b0781453df59fad5b23bf2a0d14e884482f91e09078dbe5966e0b970ba696ec4caf9aa5661802935f86717c481f1670e63f35d504a62547a9de71bfc76be2c2ae01039ebcb0f74a96f0f1f56542c8b51ef7a2a6da9ea16f23e52ecc4485b69640297a5ec6a701498d2f0e1b4e7f4b7803bf5c2eba
PAGOFACIL_TOKEN_SECRET=0C351C6679844041AA31AF9C
PAGOFACIL_CLIENT_CODE=11001
```

### 2. **Servicio PagoFacilService** (`app/Services/PagoFacilService.php`)

#### a) Método `getAccessToken()`
- Obtiene un token de autenticación de PagoFácil
- Usa los headers `tcTokenService` y `tcTokenSecret`
- Guarda el token en caché por 1 hora

#### b) Método `generateQR()`
- Genera un código QR para un pago
- Envía los datos al endpoint `/generate-qr` de PagoFácil
- Retorna el código QR y el transaction ID

#### c) Método `preparePaymentData()`
- Prepara los datos del pago según el formato de PagoFácil
- **IMPORTANTE**: Incluye `callbackUrl` que es la URL donde PagoFácil enviará la notificación cuando el cliente pague
- El `paymentNumber` se usa como identificador único (PedidoID)

### 3. **Servicio PagoService** (`app/Services/PagoService.php`)

#### Método `generateQRForPayment()`
- Se ejecuta automáticamente cuando se crea un pago con método QR (método 5)
- Genera un `paymentNumber` único: `PAGO-{id}-{timestamp}`
- Llama a PagoFacilService para generar el QR
- Guarda el QR y datos en caché

### 4. **Controlador PagoController** (`app/Http/Controllers/PagoController.php`)

#### Método `callback()` - **NUEVO**
Este es el endpoint que PagoFácil llama cuando un cliente realiza un pago.

**Recibe (POST JSON):**
```json
{
    "PedidoID": "PAGO-123-1234567890",
    "Fecha": "2025-11-24",
    "Hora": "14:30:00",
    "MetodoPago": "QR Code",
    "Estado": "Pagado"
}
```

**Responde:**
```json
{
    "error": 0,
    "status": 1,
    "message": "Notificación recibida y procesada exitosamente",
    "values": true
}
```

**Lo que hace:**
1. Busca el pago usando el `PedidoID` (que es el `paymentNumber`)
2. Actualiza el estado del pago a "Pagado" (estado = 1)
3. Actualiza la fecha de pago
4. Verifica si la venta está completamente pagada
5. Si está pagada, actualiza el estado de la venta

### 5. **Ruta del Callback** (`routes/web.php`)
```php
Route::post('pagos/callback', [PagoController::class, 'callback'])->name('pagos.callback');
```
- Esta ruta NO requiere autenticación (para que PagoFácil pueda llamarla)
- URL completa: `https://tu-dominio.com/pagos/callback`

---

## 🔄 Flujo Completo de un Pago Real

### Paso 1: Crear un Pago QR
1. Usuario crea un pago seleccionando método "QR Code" (método 5)
2. El sistema llama a `PagoService::create()`
3. Se crea el registro de pago en la base de datos
4. Se genera automáticamente el código QR llamando a PagoFácil

### Paso 2: Generar el QR
1. `PagoService` llama a `PagoFacilService::generateQR()`
2. Se preparan los datos con `preparePaymentData()` incluyendo:
   - Datos del cliente
   - Monto a pagar
   - `paymentNumber` único (ej: "PAGO-123-1234567890")
   - **`callbackUrl`**: URL donde PagoFácil notificará cuando se pague
   - Detalles de la orden
3. Se envía la petición a PagoFácil
4. PagoFácil retorna el código QR y transaction ID
5. Se guarda el QR en caché para mostrarlo al usuario

### Paso 3: Cliente Escanea y Paga
1. El cliente escanea el código QR con su app bancaria
2. Realiza el pago en su banco
3. El banco notifica a PagoFácil que el pago fue exitoso

### Paso 4: Notificación Automática (Callback)
1. PagoFácil envía una notificación POST a tu `callbackUrl`
2. El método `callback()` en `PagoController` recibe:
   - `PedidoID`: El paymentNumber que enviaste
   - `Fecha`, `Hora`, `MetodoPago`, `Estado`
3. El sistema busca el pago usando el `PedidoID`
4. Actualiza automáticamente:
   - Estado del pago a "Pagado"
   - Fecha de pago
   - Estado de la venta (si está completamente pagada)
5. Responde a PagoFácil confirmando que recibió la notificación

---

## 📝 Archivos Modificados

1. **`.env`** - Configuración de credenciales
2. **`app/Services/PagoFacilService.php`** - Servicio de integración con PagoFácil
3. **`app/Http/Controllers/PagoController.php`** - Método callback para recibir notificaciones
4. **`routes/web.php`** - Ruta pública para el callback

---

## ✅ Cómo Probar

1. **Crear un pago QR:**
   - Ve a la sección de Pagos
   - Crea un nuevo pago
   - Selecciona método "QR Code"
   - Se generará automáticamente el código QR

2. **Verificar el callback:**
   - Los logs se guardan en `storage/logs/laravel.log`
   - Busca "Callback recibido de PagoFácil" para ver las notificaciones

3. **Probar manualmente el callback:**
   ```bash
   curl -X POST http://localhost:8000/pagos/callback \
     -H "Content-Type: application/json" \
     -d '{
       "PedidoID": "PAGO-1-1234567890",
       "Fecha": "2025-11-24",
       "Hora": "14:30:00",
       "MetodoPago": "QR Code",
       "Estado": "Pagado"
     }'
   ```

---

## 🔍 Verificación

Para verificar que todo está funcionando:

1. **Revisa los logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Verifica la configuración:**
   - Los tokens están en `.env`
   - La URL del callback es accesible públicamente
   - El método callback está respondiendo correctamente

---

## ⚠️ Importante

- El `callbackUrl` debe ser una URL pública accesible desde internet
- Si estás en desarrollo local, usa un túnel como ngrok para exponer tu servidor
- El `paymentNumber` debe ser único para cada pago
- El callback debe responder rápidamente (menos de 5 segundos)

