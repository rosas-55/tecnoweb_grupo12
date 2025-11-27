<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    insumos: Array
});

const form = useForm({
    nombre: '',
    descripcion: '',
    tiempo_preparacion: '',
    insumos: []
});

const insumoSeleccionado = ref(0);
const cantidadInsumo = ref('');

const agregarInsumo = () => {
    const id = parseInt(insumoSeleccionado.value);
    const cant = parseFloat(cantidadInsumo.value);
    
    if (!id || id === 0) {
        return;
    }
    
    if (!cant || cant <= 0) {
        return;
    }
    
    const insumo = props.insumos.find(i => i.id === id);
    
    if (!insumo) {
        return;
    }
    
    form.insumos.push({
        insumo_id: id,
        cantidad: cant,
        nombre: insumo.nombre,
        unidad: insumo.unidad_medida
    });
    
    insumoSeleccionado.value = 0;
    cantidadInsumo.value = '';
};

const eliminarInsumo = (index) => {
    form.insumos.splice(index, 1);
};

const submit = () => {
    const insumosParaEnviar = form.insumos.map(insumo => ({
        insumo_id: insumo.insumo_id,
        cantidad: insumo.cantidad
    }));
    
    form.transform((data) => ({
        ...data,
        insumos: insumosParaEnviar
    })).post('/recetas', {
        preserveScroll: true,
        onSuccess: () => {
            // Receta creada exitosamente
        }
    });
};
</script>

<template>
    <MainLayout title="Crear Receta">
        <div class="max-w-4xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-primary">Crear Nueva Receta</h1>
                    <a 
                        href="/recetas"
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
                            rows="5"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                        ></textarea>
                        <p v-if="form.errors.descripcion" class="text-red-500 text-sm mt-1">{{ form.errors.descripcion }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Tiempo de Preparación (minutos)</label>
                        <input 
                            v-model="form.tiempo_preparacion" 
                            type="number"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                        >
                        <p v-if="form.errors.tiempo_preparacion" class="text-red-500 text-sm mt-1">{{ form.errors.tiempo_preparacion }}</p>
                    </div>

                    <!-- Agregar insumos -->
                    <div class="border-t border-accent/30 pt-6">
                        <h3 class="text-lg font-bold text-primary mb-4">Insumos de la Receta</h3>
                        <p v-if="form.errors.insumos" class="text-red-500 text-sm mb-4">{{ form.errors.insumos }}</p>
                        
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="col-span-2">
                                <select 
                                    v-model.number="insumoSeleccionado"
                                    class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30"
                                >
                                    <option :value="0">Seleccionar insumo...</option>
                                    <option v-for="insumo in insumos" :key="insumo.id" :value="insumo.id">
                                        {{ insumo.nombre }} ({{ insumo.unidad_medida }})
                                    </option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <input 
                                    v-model="cantidadInsumo"
                                    type="number"
                                    step="0.01"
                                    placeholder="Cantidad"
                                    class="flex-1 px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30"
                                >
                                <button 
                                    type="button"
                                    @click="agregarInsumo"
                                    class="px-4 py-2 bg-accent text-white rounded-lg hover:opacity-90"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <div v-if="form.insumos.length > 0" class="space-y-2">
                            <div 
                                v-for="(insumo, index) in form.insumos" 
                                :key="index"
                                class="flex items-center justify-between p-3 bg-accent/5 rounded-lg"
                            >
                                <span class="text-primary">{{ insumo.nombre }}</span>
                                <div class="flex items-center gap-4">
                                    <span class="text-secondary">{{ insumo.cantidad }} {{ insumo.unidad }}</span>
                                    <button 
                                        type="button"
                                        @click="eliminarInsumo(index)"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <p v-if="form.errors[`insumos.${index}.insumo_id`]" class="text-red-500 text-xs">{{ form.errors[`insumos.${index}.insumo_id`] }}</p>
                                <p v-if="form.errors[`insumos.${index}.cantidad`]" class="text-red-500 text-xs">{{ form.errors[`insumos.${index}.cantidad`] }}</p>
                            </div>
                        </div>
                        <p v-else class="text-secondary text-sm italic">No hay insumos agregados. Debe agregar al menos uno.</p>
                    </div>

                    <div class="flex gap-4">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold disabled:opacity-50"
                        >
                            {{ form.processing ? 'Guardando...' : 'Guardar Receta' }}
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
