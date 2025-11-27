<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    inventario: Object,
    insumos: Array
});

const formatDate = (date) => {
    if (!date) return '';
    // Convertir a string y extraer solo YYYY-MM-DD
    const dateStr = String(date);
    if (dateStr.includes('T')) {
        return dateStr.substring(0, 10);
    }
    if (dateStr.includes(' ')) {
        return dateStr.substring(0, 10);
    }
    return dateStr.substring(0, 10);
};

const form = useForm({
    insumo_id: props.inventario.insumo_id,
    tipo_movimiento: props.inventario.tipo_movimiento,
    cantidad: props.inventario.cantidad,
    fecha: formatDate(props.inventario.fecha),
    observacion: props.inventario.observacion || '',
    metodo_inventario: props.inventario.metodo_inventario || ''
});

const submit = () => {
    form.put(`/inventarios/${props.inventario.id}`);
};
</script>

<template>
    <MainLayout title="Editar Movimiento">
        <div class="max-w-3xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-primary">Editar Movimiento de Inventario</h1>
                    <a 
                        href="/inventarios"
                        class="px-4 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition-colors"
                    >
                        ← Volver
                    </a>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Insumo *</label>
                        <select 
                            v-model="form.insumo_id"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                        >
                            <option value="">Seleccionar insumo...</option>
                            <option v-for="insumo in insumos" :key="insumo.id" :value="insumo.id">
                                {{ insumo.nombre }} - Stock: {{ insumo.stock_actual }} {{ insumo.unidad_medida }}
                            </option>
                        </select>
                        <p v-if="form.errors.insumo_id" class="text-red-500 text-sm mt-1">{{ form.errors.insumo_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Tipo de Movimiento *</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.tipo_movimiento" value="ingreso" class="w-4 h-4">
                                <span class="text-primary">Ingreso</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.tipo_movimiento" value="salida" class="w-4 h-4">
                                <span class="text-primary">Salida</span>
                            </label>
                        </div>
                        <p v-if="form.errors.tipo_movimiento" class="text-red-500 text-sm mt-1">{{ form.errors.tipo_movimiento }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Cantidad *</label>
                            <input 
                                v-model="form.cantidad" 
                                type="number" 
                                step="0.01"
                                min="0.01"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                            <p v-if="form.errors.cantidad" class="text-red-500 text-sm mt-1">{{ form.errors.cantidad }}</p>
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
                        <label class="block text-sm font-semibold text-primary mb-2">Método de Inventario</label>
                        <input 
                            v-model="form.metodo_inventario" 
                            type="text"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                        >
                        <p v-if="form.errors.metodo_inventario" class="text-red-500 text-sm mt-1">{{ form.errors.metodo_inventario }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Observación</label>
                        <textarea 
                            v-model="form.observacion" 
                            rows="3"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                        ></textarea>
                        <p v-if="form.errors.observacion" class="text-red-500 text-sm mt-1">{{ form.errors.observacion }}</p>
                    </div>

                    <div class="flex gap-4">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold disabled:opacity-50"
                        >
                            {{ form.processing ? 'Actualizando...' : 'Actualizar Movimiento' }}
                        </button>
                        <a 
                            href="/inventarios"
                            class="px-6 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition font-semibold"
                        >
                            Cancelar
                        </a>
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
