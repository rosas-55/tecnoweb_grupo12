<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    receta: Object,
    insumos: Array
});

const form = useForm({
    nombre: props.receta.nombre,
    descripcion: props.receta.descripcion || '',
    tiempo_preparacion: props.receta.tiempo_preparacion,
    insumos: props.receta.insumos?.map(ins => ({
        insumo_id: ins.id,
        cantidad: ins.pivot.cantidad,
        nombre: ins.nombre,
        unidad: ins.unidad_medida
    })) || []
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

const obtenerPrecioGuardado = (recetaId) => {
    if (typeof window === 'undefined') {
        return null;
    }
    const almacenado = window.localStorage.getItem(`receta-precio-${recetaId}`);
    const numero = parseFloat(almacenado);
    return Number.isFinite(numero) ? numero : null;
};

const guardarPrecioEnLocal = (recetaId, valor) => {
    if (typeof window === 'undefined') {
        return;
    }

    const numero = Number(valor);
    if (!Number.isFinite(numero) || numero < 0) {
        window.localStorage.removeItem(`receta-precio-${recetaId}`);
        return;
    }

    window.localStorage.setItem(`receta-precio-${recetaId}`, numero.toString());
};

const precioManual = ref(obtenerPrecioGuardado(props.receta.id));
if (precioManual.value === null) {
    precioManual.value = 0;
}

const submit = () => {
    guardarPrecioEnLocal(props.receta.id, precioManual.value);
    const insumosParaEnviar = form.insumos.map(insumo => ({
        insumo_id: insumo.insumo_id,
        cantidad: insumo.cantidad
    }));

    form.transform((data) => ({
        ...data,
        insumos: insumosParaEnviar
    })).put(`/recetas/${props.receta.id}`);
};
</script>

<template>
    <MainLayout title="Editar Receta">
        <div class="max-w-4xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-primary">Editar Receta</h1>
                    <a
                        href="/recetas"
                        class="px-4 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition-colors"
                    >
                        ← Volver
                    </a>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Nombre de la Receta *</label>
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

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Tiempo de Preparación (minutos) *</label>
                        <input
                            v-model="form.tiempo_preparacion"
                            type="number"
                            min="1"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                        >
                        <p v-if="form.errors.tiempo_preparacion" class="text-red-500 text-sm mt-1">{{ form.errors.tiempo_preparacion }}</p>
                    </div>

                    <div class="border-t border-accent/30 pt-6">
                        <h3 class="text-lg font-bold text-primary mb-4">Ingredientes de la Receta</h3>

                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-semibold text-primary mb-2">Insumo</label>
                                <select
                                    v-model.number="insumoSeleccionado"
                                    class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                >
                                    <option :value="0">Seleccionar insumo...</option>
                                    <option v-for="insumo in insumos" :key="insumo.id" :value="insumo.id">
                                        {{ insumo.nombre }} ({{ insumo.unidad_medida }})
                                    </option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <label class="block text-sm font-semibold text-primary mb-2">Cantidad</label>
                                    <input
                                        v-model="cantidadInsumo"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        placeholder="0.00"
                                        class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                    >
                                </div>
                                <div class="flex items-end">
                                    <button
                                        type="button"
                                        @click="agregarInsumo"
                                        class="px-4 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold h-[42px]"
                                    >
                                        + Agregar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.insumos.length > 0" class="space-y-2">
                            <div
                                v-for="(insumo, index) in form.insumos"
                                :key="index"
                                class="flex items-center justify-between p-3 bg-accent/5 rounded-lg"
                            >
                                <div>
                                    <span class="text-primary font-medium">{{ insumo.nombre }}</span>
                                    <span class="text-secondary ml-2">{{ insumo.cantidad }} {{ insumo.unidad }}</span>
                                </div>
                                <button
                                    type="button"
                                    @click="eliminarInsumo(index)"
                                    class="text-red-500 hover:text-red-700 font-bold"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>

                        <p v-if="form.errors.insumos" class="text-red-500 text-sm mt-2">{{ form.errors.insumos }}</p>

                        <div class="mt-6">
                            <label class="block text-sm font-semibold text-primary mb-2">Precio base para ventas</label>
                            <input
                                v-model.number="precioManual"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            >
                            <p class="text-xs text-secondary mt-2">
                                Este importe es el que aparecerá como “Precio Unitario (Editable)” al crear nuevas ventas. Se guarda únicamente en tu navegador y siempre podrás modificarlo desde Ventas o actualizarlo aquí.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold disabled:opacity-50"
                        >
                            {{ form.processing ? 'Actualizando...' : 'Actualizar Receta' }}
                        </button>
                        <a
                            href="/recetas"
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
