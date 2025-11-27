<script setup>
import { ref, computed, onMounted } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    pago: Object
});

const qrData = ref(props.pago?.qr_data || null);
const isLoading = ref(false);
const statusMessage = ref('');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB'
    }).format(value);
};

const checkStatus = async () => {
    isLoading.value = true;
    statusMessage.value = 'Verificando estado...';

    try {
        const response = await fetch(`/pagos/${props.pago.id}/check-status`);
        const result = await response.json();

        if (result.success) {
            // Recargar la página para obtener datos actualizados
            router.reload();
        } else {
            statusMessage.value = result.message || 'Error al verificar estado';
        }
    } catch (error) {
        statusMessage.value = 'Error al verificar el estado del pago';
    } finally {
        isLoading.value = false;
    }
};

// Verificar estado automáticamente cada 10 segundos si está pendiente
let statusInterval = null;

onMounted(() => {
    if (props.pago?.metodopago === 5 && props.pago?.estado === 0) {
        statusInterval = setInterval(() => {
            checkStatus();
        }, 10000); // Cada 10 segundos
    }
});

// Limpiar intervalo al desmontar
onMounted(() => {
    return () => {
        if (statusInterval) {
            clearInterval(statusInterval);
        }
    };
});
</script>

<template>
    <MainLayout title="Código QR de Pago">
        <div class="max-w-4xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-primary">📱 Código QR para Pago</h1>
                    <Link
                        href="/pagos"
                        class="px-4 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition-colors"
                    >
                        ← Volver a Pagos
                    </Link>
                </div>

                <!-- Información del Pago -->
                <div class="bg-accent/10 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-secondary">Pago ID</p>
                            <p class="text-lg font-bold text-primary">#{{ pago.id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-secondary">Venta</p>
                            <p class="text-lg font-bold text-primary">#{{ pago.venta_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-secondary">Monto</p>
                            <p class="text-lg font-bold text-primary">{{ formatCurrency(pago.monto) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-secondary">Estado</p>
                            <span :class="[
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                pago.estado === 1 ? 'bg-green-500 text-white' : 'bg-yellow-500 text-white'
                            ]">
                                {{ pago.estado === 1 ? '✅ Pagado' : '⏳ Pendiente' }}
                            </span>
                        </div>
                    </div>
                    <div v-if="pago.venta?.cliente" class="mt-4 pt-4 border-t border-accent/20">
                        <p class="text-sm text-secondary">Cliente</p>
                        <p class="text-lg font-semibold text-primary">{{ pago.venta.cliente.name }}</p>
                        <p class="text-sm text-secondary">{{ pago.venta.cliente.email }}</p>
                    </div>
                </div>

                <!-- Código QR -->
                <div v-if="qrData && qrData.qr_code" class="text-center">
                    <div class="bg-white p-6 rounded-lg inline-block mb-4">
                        <img
                            :src="qrData.qr_code"
                            alt="Código QR de Pago"
                            class="w-64 h-64 mx-auto"
                        />
                    </div>

                    <div class="space-y-4">
                        <p class="text-lg font-semibold text-primary">
                            Escanea este código QR con tu aplicación bancaria para realizar el pago
                        </p>

                        <div class="bg-yellow-100 border border-yellow-400 rounded-lg p-4">
                            <p class="text-sm text-yellow-800">
                                <strong>⚠️ Importante:</strong> El pago se confirmará automáticamente cuando el cliente realice el pago.
                                No es necesario actualizar manualmente.
                            </p>
                        </div>

                        <!-- Información adicional del QR -->
                        <div v-if="qrData.payment_number" class="bg-accent/10 rounded-lg p-4">
                            <p class="text-sm text-secondary">Número de Pago:</p>
                            <p class="text-lg font-mono font-bold text-primary">{{ qrData.payment_number }}</p>
                        </div>

                        <!-- Botón para verificar estado -->
                        <div class="flex gap-4 justify-center">
                            <button
                                @click="checkStatus"
                                :disabled="isLoading || pago.estado === 1"
                                class="px-6 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold disabled:opacity-50"
                            >
                                {{ isLoading ? 'Verificando...' : '🔄 Verificar Estado' }}
                            </button>
                        </div>

                        <p v-if="statusMessage" class="text-sm text-secondary">{{ statusMessage }}</p>
                    </div>
                </div>

                <!-- Error o QR no generado -->
                <div v-else class="text-center py-12">
                    <div class="bg-red-100 border border-red-400 rounded-lg p-6 inline-block">
                        <p class="text-red-800 font-semibold text-lg mb-2">❌ Error al generar código QR</p>
                        <p class="text-red-700 text-sm mb-4">
                            No se pudo generar el código QR. Por favor, verifica la configuración de PagoFácil.
                        </p>
                        <Link
                            href="/pagos"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors inline-block"
                        >
                            Volver a Pagos
                        </Link>
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-blue-900 mb-4">📋 Instrucciones para el Cliente:</h3>
                    <ol class="list-decimal list-inside space-y-2 text-blue-800">
                        <li>Abre la aplicación de tu banco (Bancar, BNB, Banco Unión, etc.)</li>
                        <li>Busca la opción "Pagar con QR" o "Escanear QR"</li>
                        <li>Escanea el código QR mostrado arriba</li>
                        <li>Confirma el monto y completa el pago</li>
                        <li>El sistema actualizará automáticamente el estado del pago</li>
                    </ol>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.bg-card {
    background-color: var(--color-sidebar);
    border: 1px solid var(--color-accent);
}
</style>

