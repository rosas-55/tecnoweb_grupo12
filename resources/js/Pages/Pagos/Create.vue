<script setup>
import { computed, watch } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    ventas: Array
});

const form = useForm({
    venta_id: '',
    monto: '',
    metodopago: 1,
    fechapago: new Date().toISOString().split('T')[0],
    estado: 1
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB'
    }).format(value);
};

const ventaSeleccionada = computed(() => {
    if (!form.venta_id) {
        return null;
    }

    return props.ventas.find((venta) => venta.id === Number(form.venta_id)) || null;
});

watch(
    () => form.venta_id,
    () => {
        if (ventaSeleccionada.value) {
            const saldo = Number(ventaSeleccionada.value.saldo_pendiente ?? ventaSeleccionada.value.total ?? 0);
            form.monto = saldo > 0 ? Number(saldo.toFixed(2)) : Number((ventaSeleccionada.value.total ?? 0).toFixed(2));
        } else {
            form.monto = '';
        }
    }
);

const submit = () => {
    form.post('/pagos');
};

const montoActual = computed(() => {
    const valor = Number(form.monto);
    return Number.isFinite(valor) ? valor : 0;
});

const pagadoAntes = computed(() => {
    if (!ventaSeleccionada.value) {
        return 0;
    }
    return Number(ventaSeleccionada.value.total_pagado || 0);
});

const pagadoDespues = computed(() => {
    if (!ventaSeleccionada.value) {
        return 0;
    }
    const total = Number(ventaSeleccionada.value.total || 0);
    const suma = pagadoAntes.value + montoActual.value;
    return Math.min(suma, total);
});

const saldoDespues = computed(() => {
    if (!ventaSeleccionada.value) {
        return 0;
    }
    const total = Number(ventaSeleccionada.value.total || 0);
    return Math.max(total - pagadoDespues.value, 0);
});

// Detectar si se seleccionó QR Code
const isQRPayment = computed(() => form.metodopago === 5);

watch(
    () => form.metodopago,
    (nuevo) => {
        if (nuevo === 5) {
            form.estado = 0;
        } else if (form.estado === 0) {
            form.estado = 1;
        }
    },
    { immediate: true }
);
</script>

<template>
    <MainLayout title="Registrar Pago">
        <div class="max-w-3xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-primary">Registrar Nuevo Pago</h1>
                    <a
                        href="/pagos"
                        class="px-4 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition-colors"
                    >
                        ← Volver
                    </a>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Venta *</label>
                        <select
                            v-model="form.venta_id"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                        >
                            <option value="">Seleccionar venta...</option>
                            <option v-for="venta in ventas" :key="venta.id" :value="venta.id">
                                Venta #{{ venta.id }} - {{ venta.cliente?.name }} - {{ formatCurrency(venta.total) }}
                            </option>
                        </select>
                        <p v-if="form.errors.venta_id" class="text-red-500 text-sm mt-1">{{ form.errors.venta_id }}</p>

                        <div
                            v-if="ventaSeleccionada"
                            class="mt-3 p-4 rounded-lg border border-accent/30 bg-accent/5 text-sm text-secondary space-y-2"
                        >
                            <div class="flex justify-between">
                                <span>Total de la venta:</span>
                                <span class="font-semibold text-primary">{{ formatCurrency(ventaSeleccionada.total) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Pagado antes de este registro:</span>
                                <span class="font-semibold text-green-600">{{ formatCurrency(pagadoAntes) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Monto que registrarás ahora:</span>
                                <span class="font-semibold text-blue-600">{{ formatCurrency(montoActual) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total pagado después de guardar:</span>
                                <span class="font-semibold text-green-700">{{ formatCurrency(pagadoDespues) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Saldo pendiente tras este pago:</span>
                                <span
                                    :class="[
                                        'font-semibold',
                                        saldoDespues > 0 ? 'text-red-500' : 'text-green-600'
                                    ]"
                                >
                                    {{ formatCurrency(saldoDespues) }}
                                </span>
                            </div>
                            <p class="text-xs mt-2 text-secondary">
                                El campo "Monto" se llena con el saldo pendiente actual, pero puedes ajustarlo si el cliente abona solo una parte. El resumen se actualiza en tiempo real.
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Método de Pago *</label>
                        <select
                            v-model="form.metodopago"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                        >
                            <option :value="1">💵 Efectivo</option>
                            <option :value="2">💳 Tarjeta de Crédito/Débito</option>
                            <option :value="3">🏦 Transferencia Bancaria</option>
                            <option :value="4">📄 Cheque</option>
                            <option :value="5">📱 QR Code (PagoFácil)</option>
                        </select>
                        <p v-if="form.errors.metodopago" class="text-red-500 text-sm mt-1">{{ form.errors.metodopago }}</p>

                        <!-- Información sobre PagoFácil QR -->
                        <div v-if="isQRPayment" class="mt-4 p-4 bg-purple-100 border border-purple-300 rounded-lg">
                            <div class="flex items-start gap-3">
                                <div class="text-2xl">📱</div>
                                <div>
                                    <h3 class="font-bold text-purple-900 mb-2">Pago con QR Code - PagoFácil</h3>
                                    <p class="text-sm text-purple-800 mb-2">
                                        Al registrar este pago, se generará automáticamente un código QR que el cliente podrá escanear con su aplicación bancaria para realizar el pago.
                                    </p>
                                    <ul class="text-xs text-purple-700 space-y-1 list-disc list-inside">
                                        <li>El código QR se generará automáticamente</li>
                                        <li>El cliente escanea el QR con su app bancaria</li>
                                        <li>El sistema recibirá la confirmación automáticamente</li>
                                        <li>El estado se actualizará cuando el pago sea confirmado</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Monto *</label>
                            <input
                                v-model="form.monto"
                                type="number"
                                step="0.01"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                            <p v-if="form.errors.monto" class="text-red-500 text-sm mt-1">{{ form.errors.monto }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Fecha *</label>
                            <input
                                v-model="form.fechapago"
                                type="date"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                            <p v-if="form.errors.fechapago" class="text-red-500 text-sm mt-1">{{ form.errors.fechapago }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Estado del Pago *</label>
                        <select
                            v-model="form.estado"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                            :disabled="isQRPayment"
                        >
                            <option :value="0">Pendiente</option>
                            <option :value="1">Pagado</option>
                        </select>
                        <p v-if="form.errors.estado" class="text-red-500 text-sm mt-1">{{ form.errors.estado }}</p>
                        <p v-if="isQRPayment" class="text-sm text-purple-600 mt-1">
                            ℹ️ Para pagos QR, el estado se establecerá automáticamente como "Pendiente" y se actualizará cuando se confirme el pago.
                        </p>
                    </div>

                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            :class="[
                                'px-6 py-2 rounded-lg hover:opacity-90 transition font-semibold disabled:opacity-50',
                                isQRPayment
                                    ? 'bg-purple-600 text-white'
                                    : 'bg-accent text-white'
                            ]"
                        >
                            {{ form.processing
                                ? 'Generando QR...'
                                : isQRPayment
                                    ? '📱 Generar Código QR'
                                    : 'Registrar Pago'
                            }}
                        </button>
                    </div>
                </form>
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
