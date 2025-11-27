<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    nombre: '',
    descripcion: '',
    unidad_medida: '',
    costo_unitario: '',
    stock_actual: '',
    stock_minimo: ''
});

const submit = () => {
    form.post('/insumos');
};
</script>

<template>
    <MainLayout title="Crear Insumo">
        <div class="max-w-3xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-primary">Crear Nuevo Insumo</h1>
                    <a 
                        href="/insumos"
                        class="px-4 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition-colors"
                    >
                        ← Volver
                    </a>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Nombre *</label>
                        <input 
                            v-model="form.nombre" 
                            type="text" 
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                        >
                        <p v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">{{ form.errors.nombre }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Descripción</label>
                        <textarea 
                            v-model="form.descripcion" 
                            rows="3"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                        ></textarea>
                        <p v-if="form.errors.descripcion" class="text-red-500 text-sm mt-1">{{ form.errors.descripcion }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Unidad de Medida *</label>
                            <select 
                                v-model="form.unidad_medida"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                                <option value="">Seleccionar...</option>
                                <option value="kg">Kilogramos (kg)</option>
                                <option value="g">Gramos (g)</option>
                                <option value="L">Litros (L)</option>
                                <option value="ml">Mililitros (ml)</option>
                                <option value="unidad">Unidades</option>
                            </select>
                            <p v-if="form.errors.unidad_medida" class="text-red-500 text-sm mt-1">{{ form.errors.unidad_medida }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Costo Unitario *</label>
                            <input 
                                v-model="form.costo_unitario" 
                                type="number" 
                                step="0.01"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                            <p v-if="form.errors.costo_unitario" class="text-red-500 text-sm mt-1">{{ form.errors.costo_unitario }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Stock Actual *</label>
                            <input 
                                v-model="form.stock_actual" 
                                type="number" 
                                step="0.01"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                            <p v-if="form.errors.stock_actual" class="text-red-500 text-sm mt-1">{{ form.errors.stock_actual }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary mb-2">Stock Mínimo *</label>
                            <input 
                                v-model="form.stock_minimo" 
                                type="number" 
                                step="0.01"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                required
                            >
                            <p v-if="form.errors.stock_minimo" class="text-red-500 text-sm mt-1">{{ form.errors.stock_minimo }}</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold disabled:opacity-50"
                        >
                            {{ form.processing ? 'Guardando...' : 'Guardar Insumo' }}
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
