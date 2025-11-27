<script setup>
import { computed, ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    producciones: Object,
    search: String
});

const searchQuery = ref(props.search || '');
const modalAbierto = ref(false);
const produccionSeleccionada = ref(null);

const handleSearch = (query) => {
    router.get('/produccion', { search: query }, {
        preserveState: true,
        preserveScroll: true
    });
};

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const fechaSolo = fecha.substring(0, 10);
    const [year, month, day] = fechaSolo.split('-');
    const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    return `${parseInt(day, 10)} de ${meses[parseInt(month, 10) - 1]} de ${year},00:00`;
};

const deleteProduccion = (id) => {
    if (confirm('¿Está seguro de eliminar esta producción?')) {
        router.delete(`/produccion/${id}`);
    }
};

const mostrarDetalleInsumos = (produccion) => {
    produccionSeleccionada.value = produccion;
    modalAbierto.value = true;
};

const cerrarModal = () => {
    modalAbierto.value = false;
    produccionSeleccionada.value = null;
};

const detalleInsumos = computed(() => {
    if (!produccionSeleccionada.value?.receta?.insumos?.length) return [];

    const cantidad = Number(produccionSeleccionada.value.cantidad_producida) || 0;

    return produccionSeleccionada.value.receta.insumos.map((insumo) => {
        const base = Number(insumo.pivot?.cantidad || 0);
        return {
            id: insumo.id,
            nombre: insumo.nombre,
            unidad: insumo.unidad_medida,
            porLote: base,
            total: base * cantidad
        };
    });
});

const formatearCantidad = (valor) => {
    if (Number.isNaN(valor)) return '0';
    if (Number.isInteger(valor)) return valor.toString();
    return Number(valor).toFixed(2).replace(/\.?0+$/, '');
};
</script>

<template>
    <MainLayout
        title="Producción"
        search-placeholder="Buscar producciones por receta o responsable..."
        :on-search="handleSearch"
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-primary">🏭 Control de Producción</h1>
                <Link
                    href="/produccion/create"
                    class="px-4 py-2 bg-accent text-white rounded-lg hover:bg-accent/80 transition-colors"
                >
                    ➕ Nueva Producción
                </Link>
            </div>

            <!-- Tabla de producciones -->
            <div class="bg-card rounded-lg shadow-lg overflow-hidden">
                <div v-if="producciones.data.length === 0" class="p-8 text-center text-secondary">
                    <div class="text-6xl mb-4">📦</div>
                    <p class="text-lg">No hay registros de producción</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-accent text-white">
                            <tr>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">#</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">📅 Fecha</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">🍽️ Receta</th>
                                <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider">📊 Cantidad</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">👤 Responsable</th>
                                <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider">⚙️ Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-accent/20">
                            <tr v-for="prod in producciones.data" :key="prod.id" class="hover:bg-accent/5 transition-colors">
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-accent/20 text-accent font-bold text-sm">
                                        {{ prod.id }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm">
                                        <div class="text-primary font-medium">{{ formatearFecha(prod.fecha).split(',')[0] }}</div>
                                        <div class="text-secondary text-xs">{{ formatearFecha(prod.fecha).split(',')[1] }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div>
                                        <div class="text-primary font-bold text-base">{{ prod.receta?.nombre || 'Sin receta' }}</div>
                                        <div v-if="prod.receta?.descripcion" class="text-secondary text-xs mt-1 line-clamp-1">
                                            {{ prod.receta.descripcion }}
                                        </div>
                                        <div v-if="prod.receta?.tiempo_preparacion" class="text-xs text-secondary mt-1">
                                            ⏱️ {{ prod.receta.tiempo_preparacion }} min
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-green-500/20 text-green-700 font-bold text-lg">
                                        {{ prod.cantidad_producida }}
                                    </span>
                                    <div class="text-xs text-secondary mt-1">unidades</div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-accent/20 flex items-center justify-center text-accent font-bold">
                                            {{ prod.responsable?.name?.charAt(0).toUpperCase() || '?' }}
                                        </div>
                                        <div>
                                            <div class="text-primary font-medium text-sm">{{ prod.responsable?.name || 'Sin asignar' }}</div>
                                            <div class="text-secondary text-xs">{{ prod.responsable?.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2 flex-wrap">
                                        <button
                                            v-if="prod.receta?.insumos?.length"
                                            @click="mostrarDetalleInsumos(prod)"
                                            class="px-3 py-2 bg-blue-500/20 text-blue-600 rounded-lg hover:bg-blue-500/30 transition-colors font-medium text-sm"
                                            title="Ver insumos consumidos"
                                        >
                                            📋 Ver insumos
                                        </button>
                                        <Link
                                            :href="`/produccion/${prod.id}/edit`"
                                            class="px-3 py-2 bg-amber-500/20 text-amber-600 rounded-lg hover:bg-amber-500/30 transition-colors font-medium text-sm"
                                            title="Editar producción"
                                        >
                                            ✏️ Editar
                                        </Link>
                                        <button
                                            @click="deleteProduccion(prod.id)"
                                            class="px-3 py-2 bg-red-500/20 text-red-600 rounded-lg hover:bg-red-500/30 transition-colors font-medium text-sm"
                                            title="Eliminar producción"
                                        >
                                            🗑️ Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="producciones.links" class="flex justify-center gap-2">
                <template v-for="(link, index) in producciones.links" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors',
                            link.active
                                ? 'bg-accent text-white'
                                : 'bg-card text-primary hover:bg-accent/20'
                        ]"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        :class="[
                            'px-4 py-2 rounded-lg opacity-50 cursor-not-allowed',
                            link.active ? 'bg-accent text-white' : 'bg-card text-primary'
                        ]"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>

        <!-- Modal Detalle de Insumos -->
        <div
            v-if="modalAbierto"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 px-4"
        >
            <div class="bg-card rounded-2xl shadow-2xl max-w-2xl w-full border border-accent/30">
                <div class="flex items-start justify-between p-6 border-b border-accent/20">
                    <div>
                        <p class="text-sm uppercase tracking-widest text-secondary mb-1">Detalle de Insumos</p>
                        <h3 class="text-xl font-bold text-primary">
                            {{ produccionSeleccionada?.receta?.nombre || 'Receta no disponible' }}
                        </h3>
                        <p class="text-secondary text-sm mt-1">
                            Cantidad producida: <span class="font-semibold text-primary">{{ produccionSeleccionada?.cantidad_producida }}</span> unidad(es)
                        </p>
                    </div>
                    <button
                        class="text-secondary hover:text-primary transition-colors text-xl font-bold"
                        @click="cerrarModal"
                    >
                        ×
                    </button>
                </div>

                <div class="p-6 max-h-[60vh] overflow-y-auto">
                    <template v-if="detalleInsumos.length">
                        <ul class="space-y-3">
                            <li
                                v-for="insumo in detalleInsumos"
                                :key="insumo.id"
                                class="flex items-center justify-between bg-sidebar/40 border border-accent/20 rounded-xl px-4 py-3"
                            >
                                <div>
                                    <p class="text-primary font-semibold">{{ insumo.nombre }}</p>
                                    <p class="text-xs text-secondary">
                                        Base por unidad: {{ formatearCantidad(insumo.porLote) }} {{ insumo.unidad }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-accent">
                                        {{ formatearCantidad(insumo.total) }} {{ insumo.unidad }}
                                    </p>
                                    <p class="text-xs text-secondary">Total consumido</p>
                                </div>
                            </li>
                        </ul>
                    </template>
                    <p v-else class="text-center text-secondary text-sm">
                        Esta producción no tiene insumos asociados en la receta.
                    </p>
                </div>

                <div class="px-6 py-4 border-t border-accent/20 flex justify-end">
                    <button
                        class="px-5 py-2 rounded-lg bg-accent text-white font-semibold hover:opacity-90 transition"
                        @click="cerrarModal"
                    >
                        Entendido
                    </button>
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
