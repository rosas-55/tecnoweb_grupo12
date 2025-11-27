<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    clientes: Array,
    recetas: Array
});

const form = useForm({
    cliente_id: '',
    fecha: new Date().toISOString().split('T')[0],
    tipo: '1',
    detalles: []
});

const recetaSeleccionada = ref(null);
const cantidadReceta = ref('');
const precioReceta = ref(0);

const leerPrecioManualReceta = (recetaId) => {
    if (typeof window === 'undefined') {
        return null;
    }

    const almacenado = window.localStorage.getItem(`receta-precio-${recetaId}`);
    const numero = parseFloat(almacenado);
    return Number.isFinite(numero) ? numero : null;
};

const getRecetaInfo = (receta) => {
    const insumos = receta.insumos?.length || 0;
    const tiempo = receta.tiempo_preparacion || 0;
    return `${insumos} ingredientes • ${tiempo} min`;
};

// Observar cambios en la receta seleccionada para actualizar el precio
const onRecetaChange = () => {
    if (!recetaSeleccionada.value) {
        precioReceta.value = 0;
        return;
    }

    const receta = props.recetas.find(r => r.id == recetaSeleccionada.value);
    if (!receta) {
        precioReceta.value = 0;
        return;
    }

    const precioManual = leerPrecioManualReceta(receta.id);
    precioReceta.value = precioManual !== null ? Number(precioManual.toFixed(2)) : 0;
};

const agregarDetalle = () => {
    if (recetaSeleccionada.value && cantidadReceta.value > 0 && precioReceta.value > 0) {
        const receta = props.recetas.find(r => r.id == recetaSeleccionada.value);
        const precioUnitario = parseFloat(precioReceta.value || 0);
        form.detalles.push({
            receta_id: receta.id,
            nombre: receta.nombre,
            descripcion: receta.descripcion,
            tiempo_preparacion: receta.tiempo_preparacion,
            insumos_count: receta.insumos?.length || 0,
            cantidad: parseInt(cantidadReceta.value),
            precio_unitario: precioUnitario,
            subtotal: parseInt(cantidadReceta.value) * precioUnitario
        });
        recetaSeleccionada.value = null;
        cantidadReceta.value = '';
        precioReceta.value = 0;
    }
};

const eliminarDetalle = (index) => {
    form.detalles.splice(index, 1);
};

const calcularTotal = () => {
    return form.detalles.reduce((sum, d) => sum + d.subtotal, 0);
};

const totalTemporal = computed(() => {
    const cantidad = Number(cantidadReceta.value);
    const precio = Number(precioReceta.value);
    if (!Number.isFinite(cantidad) || !Number.isFinite(precio)) {
        return 0;
    }
    return cantidad * precio;
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2
    }).format(value || 0);
};

const submit = () => {
    form.post('/ventas');
};
</script>

<template>
    <MainLayout title="Crear Venta">
        <div class="max-w-4xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-primary">Registrar Nueva Venta</h1>
                    <a
                        href="/ventas"
                        class="px-4 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition-colors"
                    >
                        ← Volver
                    </a>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Cliente *</label>
                            <select
                                v-model="form.cliente_id"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                                <option value="">Seleccionar cliente...</option>
                                <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                    {{ cliente.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.cliente_id" class="text-red-500 text-sm mt-1">{{ form.errors.cliente_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Fecha *</label>
                            <input
                                v-model="form.fecha"
                                type="date"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                            <p v-if="form.errors.fecha" class="text-red-500 text-sm mt-1">{{ form.errors.fecha }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Tipo de Pago *</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.tipo" value="1" class="w-4 h-4">
                                <span class="text-primary">Contado</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.tipo" value="2" class="w-4 h-4">
                                <span class="text-primary">Crédito</span>
                            </label>
                        </div>
                        <p v-if="form.errors.tipo" class="text-red-500 text-sm mt-1">{{ form.errors.tipo }}</p>
                    </div>

                    <!-- Agregar productos -->
                    <div class="border-t border-accent/30 pt-6">
                        <h3 class="text-lg font-bold text-primary mb-4">📋 Productos de la Venta</h3>

                        <div class="bg-accent/5 p-4 rounded-lg mb-4">
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-primary mb-2">Producto / Receta</label>
                                <select
                                    v-model="recetaSeleccionada"
                                    @change="onRecetaChange"
                                    class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:border-accent focus:outline-none"
                                >
                                    <option value="">🔍 Seleccionar producto...</option>
                                    <option v-for="receta in recetas" :key="receta.id" :value="receta.id">
                                        {{ receta.nombre }} - {{ getRecetaInfo(receta) }}
                                    </option>
                                </select>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-semibold text-primary mb-2">Cantidad</label>
                                    <input
                                        v-model="cantidadReceta"
                                        type="number"
                                        min="1"
                                        placeholder="0"
                                        class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:border-accent focus:outline-none"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-primary mb-2">Precio Unitario (Editable)</label>
                                    <input
                                        v-model="precioReceta"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:border-accent focus:outline-none"
                                    >
                                </div>
                                <div class="flex items-end">
                                    <button
                                        type="button"
                                        @click="agregarDetalle"
                                        class="w-full px-4 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold"
                                    >
                                        ➕ Agregar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.detalles.length > 0" class="space-y-3">
                            <h4 class="text-sm font-semibold text-primary mb-2">Productos agregados:</h4>
                            <div
                                v-for="(detalle, index) in form.detalles"
                                :key="index"
                                class="p-4 bg-accent/5 rounded-lg border border-accent/20 hover:border-accent/40 transition"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <h5 class="text-primary font-bold text-lg mb-1">{{ detalle.nombre }}</h5>
                                        <p v-if="detalle.descripcion" class="text-secondary text-sm mb-2">{{ detalle.descripcion }}</p>
                                        <div class="flex gap-4 text-xs text-secondary">
                                            <span class="flex items-center gap-1">
                                                <span class="font-semibold">🍳</span> {{ detalle.insumos_count }} ingredientes
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <span class="font-semibold">⏱️</span> {{ detalle.tiempo_preparacion }} min
                                            </span>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="eliminarDetalle(index)"
                                        class="text-red-500 hover:text-red-700 font-bold text-xl px-2"
                                        title="Eliminar producto"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <div class="flex items-center justify-between pt-3 border-t border-accent/20">
                                    <div class="flex items-center gap-6">
                                        <span class="text-sm text-secondary">Cantidad: <span class="font-bold text-primary">{{ detalle.cantidad }}</span></span>
                                        <span class="text-sm text-secondary">Precio unitario: <span class="font-bold text-primary">{{ formatCurrency(detalle.precio_unitario) }}</span></span>
                                    </div>
                                    <span class="text-lg font-bold text-accent">{{ formatCurrency(detalle.subtotal) }}</span>
                                </div>
                            </div>

                            <div class="flex justify-end items-center p-4 bg-accent/10 rounded-lg">
                                <span class="text-lg font-bold text-primary mr-4">TOTAL:</span>
                                <span class="text-2xl font-bold text-accent">{{ formatCurrency(calcularTotal()) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing || form.detalles.length === 0"
                            class="px-6 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold disabled:opacity-50"
                        >
                            {{ form.processing ? 'Guardando...' : 'Registrar Venta' }}
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
