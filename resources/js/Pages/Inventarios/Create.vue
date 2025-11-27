<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    insumos: Array
});

const form = useForm({
    insumo_id: '',
    tipo_movimiento: 'ingreso',
    cantidad: '',
    fecha: new Date().toISOString().split('T')[0],
    observacion: '',
    metodo_inventario: ''
});

const submit = () => {
    form.post('/inventarios');
};
</script>

<template>
    <MainLayout title="Registrar Movimiento">
        <div class="max-w-3xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-primary">Registrar Movimiento de Inventario</h1>
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
                        <select 
                            v-model="form.tipo_movimiento"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                        >
                            <option value="ingreso">Ingreso (Compra/Devolución)</option>
                            <option value="salida">Salida (Uso/Merma)</option>
                        </select>
                        <p v-if="form.errors.tipo_movimiento" class="text-red-500 text-sm mt-1">{{ form.errors.tipo_movimiento }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Cantidad *</label>
                            <input 
                                v-model="form.cantidad" 
                                type="number" 
                                step="0.01"
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
                        <label class="block text-sm font-semibold text-primary mb-2">Observaciones</label>
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
                            {{ form.processing ? 'Guardando...' : 'Registrar Movimiento' }}
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
