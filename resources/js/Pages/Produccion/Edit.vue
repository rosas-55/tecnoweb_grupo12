<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    produccion: Object,
    recetas: Array
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
    receta_id: props.produccion.receta_id,
    cantidad_producida: props.produccion.cantidad_producida,
    fecha: formatDate(props.produccion.fecha)
});

const getRecetaInfo = (receta) => {
    const insumos = receta.insumos?.length || 0;
    const tiempo = receta.tiempo_preparacion || 0;
    return `${insumos} ingredientes • ${tiempo} min`;
};

const submit = () => {
    form.put(`/produccion/${props.produccion.id}`);
};
</script>

<template>
    <MainLayout title="Editar Producción">
        <div class="max-w-3xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-primary">Editar Producción</h1>
                    <a 
                        href="/produccion"
                        class="px-4 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition-colors"
                    >
                        ← Volver
                    </a>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
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
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Cantidad Producida *</label>
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
                            <label class="block text-sm font-semibold text-primary mb-2">Fecha de Producción *</label>
                            <input 
                                v-model="form.fecha" 
                                type="date"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                            <p v-if="form.errors.fecha" class="text-red-500 text-sm mt-1">{{ form.errors.fecha }}</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold disabled:opacity-50"
                        >
                            {{ form.processing ? 'Actualizando...' : 'Actualizar Producción' }}
                        </button>
                        <a 
                            href="/produccion"
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
