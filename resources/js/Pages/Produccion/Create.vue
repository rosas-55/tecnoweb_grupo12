<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    recetas: Array,
    usuario: Object
});

const form = useForm({
    receta_id: '',
    cantidad_producida: 1,
    fecha: new Date().toISOString().split('T')[0]
});

const getRecetaInfo = (receta) => {
    const insumos = receta.insumos?.length || 0;
    const tiempo = receta.tiempo_preparacion ? `${receta.tiempo_preparacion} min` : 'N/A';
    return `${insumos} insumos • Tiempo: ${tiempo}`;
};

const recetaSeleccionada = computed(() => {
    if (!form.receta_id) return null;
    return props.recetas.find((receta) => Number(receta.id) === Number(form.receta_id)) || null;
});

const cantidadSolicitada = computed(() => {
    const value = Number(form.cantidad_producida);
    return Number.isNaN(value) || value < 1 ? 0 : value;
});

const insumosCalculados = computed(() => {
    if (!recetaSeleccionada.value?.insumos?.length || cantidadSolicitada.value === 0) return [];

    return recetaSeleccionada.value.insumos.map((insumo) => {
        const base = Number(insumo.pivot?.cantidad || 0);
        const stockActual = Number(insumo.stock_actual ?? 0);
        const total = base * cantidadSolicitada.value;

        return {
            id: insumo.id,
            nombre: insumo.nombre,
            unidad: insumo.unidad_medida,
            porUnidad: base,
            total,
            stockActual,
            falta: Math.max(total - stockActual, 0)
        };
    });
});

const tieneReceta = computed(() => !!recetaSeleccionada.value);
const recetaSinInsumos = computed(() => tieneReceta.value && !recetaSeleccionada.value?.insumos?.length);

const existenFaltantes = computed(() =>
    insumosCalculados.value.some((item) => item.falta > 0)
);

const formatoCantidad = (valor) => {
    if (Number.isNaN(valor)) return '0';
    if (Number.isInteger(valor)) return valor.toString();
    return Number(valor).toFixed(2).replace(/\.?0+$/, '');
};

const submit = () => {
    form.post('/produccion');
};
</script>

<template>
    <MainLayout title="Registrar Producción">
        <div class="max-w-3xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-primary">Registrar Nueva Producción</h1>
                    <a
                        href="/produccion"
                        class="px-4 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition-colors"
                    >
                        ← Volver
                    </a>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div v-if="$page.props.errors?.general" class="p-3 rounded-lg border border-red-400 bg-red-50 text-red-700">
                        {{ $page.props.errors.general }}
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Receta *</label>
                        <select
                            v-model="form.receta_id"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                        >
                            <option value="">Seleccionar receta...</option>
                            <option v-for="receta in recetas" :key="receta.id" :value="receta.id">
                                {{ receta.nombre }} - {{ getRecetaInfo(receta) }}
                            </option>
                        </select>
                        <p v-if="form.errors.receta_id" class="text-red-500 text-sm mt-1">{{ form.errors.receta_id }}</p>

                        <div v-if="form.receta_id" class="mt-3 p-3 bg-accent/5 rounded-lg">
                            <p class="text-sm text-secondary">
                                <strong>Nota:</strong> Esta producción consumirá los insumos necesarios del inventario automáticamente.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Cantidad a Producir *</label>
                            <input
                                v-model="form.cantidad_producida"
                                type="number"
                                min="1"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                            <p v-if="form.errors.cantidad_producida" class="text-red-500 text-sm mt-1">{{ form.errors.cantidad_producida }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Responsable</label>
                            <div class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30">
                                👤 {{ usuario?.name || 'Usuario actual' }}
                            </div>
                            <p class="text-xs text-secondary mt-1">Se registrará automáticamente</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border border-accent/30 bg-accent/5">
                        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-secondary font-semibold">Previsualización</p>
                                <h3 class="text-lg font-bold text-primary">Consumo estimado</h3>
                                <p class="text-sm text-secondary">
                                    Selecciona una receta y cantidad para calcular el consumo.
                                </p>
                            </div>
                            <div class="text-right">
                                <span
                                    v-if="existenFaltantes"
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold"
                                >
                                    ⚠️ Faltan insumos
                                </span>
                                <span
                                    v-else-if="insumosCalculados.length"
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold"
                                >
                                    ✅ Stock suficiente
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent/20 text-primary text-xs font-semibold"
                                >
                                    ⚙️ Esperando selección
                                </span>
                            </div>
                        </div>

                        <template v-if="!tieneReceta">
                            <p class="text-sm text-secondary">Selecciona una receta para ver la lista de insumos que se descontarán del inventario.</p>
                        </template>

                        <template v-else-if="recetaSinInsumos">
                            <p class="text-sm text-secondary text-center">
                                Esta receta no tiene insumos asociados. Se registrará la producción sin movimientos de inventario.
                            </p>
                        </template>

                        <template v-else>
                            <p class="text-sm text-secondary mb-4">
                                Se consumirá el inventario para <strong>{{ cantidadSolicitada || 0 }}</strong> unidad(es) de
                                "<strong>{{ recetaSeleccionada?.nombre }}</strong>".
                            </p>
                            <ul class="space-y-2">
                                <li
                                    v-for="insumo in insumosCalculados"
                                    :key="insumo.id"
                                    class="flex items-center justify-between rounded-lg px-4 py-3 border border-accent/10 bg-accent/10"
                                >
                                    <div>
                                        <p class="text-primary font-semibold">{{ insumo.nombre }}</p>
                                        <p class="text-xs text-secondary">
                                            Por unidad: {{ formatoCantidad(insumo.porUnidad) }} {{ insumo.unidad }}
                                        </p>
                                        <p class="text-xs text-secondary">
                                            Disponible: {{ formatoCantidad(insumo.stockActual) }} {{ insumo.unidad }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="text-lg font-bold"
                                            :class="insumo.falta > 0 ? 'text-red-600' : 'text-accent'"
                                        >
                                            {{ formatoCantidad(insumo.total) }} {{ insumo.unidad }}
                                        </p>
                                        <p class="text-xs text-secondary">
                                            Cantidad total
                                            <span v-if="insumo.falta > 0" class="block text-red-600 font-semibold">
                                                Falta {{ formatoCantidad(insumo.falta) }} {{ insumo.unidad }}
                                            </span>
                                        </p>
                                    </div>
                                </li>
                            </ul>
                        </template>
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

                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold disabled:opacity-50"
                        >
                            {{ form.processing ? 'Guardando...' : 'Registrar Producción' }}
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
