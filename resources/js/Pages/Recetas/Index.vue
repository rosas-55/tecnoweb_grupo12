<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    recetas: Object,
    search: String
});

const searchQuery = ref(props.search || '');

const handleSearch = (query) => {
    router.get('/recetas', { search: query }, {
        preserveState: true,
        preserveScroll: true
    });
};

const deleteReceta = (id) => {
    if (confirm('¿Está seguro de eliminar esta receta?')) {
        router.delete(`/recetas/${id}`);
    }
};

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2
    }).format(valor || 0);
};

const obtenerPrecioManual = (recetaId) => {
    if (typeof window === 'undefined') {
        return null;
    }

    const almacenado = window.localStorage.getItem(`receta-precio-${recetaId}`);
    const numero = parseFloat(almacenado);
    return Number.isFinite(numero) ? numero : null;
};

</script>

<template>
    <MainLayout
        title="Recetas"
        search-placeholder="Buscar recetas por nombre..."
        :on-search="handleSearch"
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-primary">📝 Gestión de Recetas</h1>
                <Link
                    href="/recetas/create"
                    class="px-4 py-2 bg-accent text-white rounded-lg hover:bg-accent/80 transition-colors"
                >
                    ➕ Nueva Receta
                </Link>
            </div>

            <!-- Grid de recetas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="receta in recetas.data"
                    :key="receta.id"
                    class="bg-card rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow"
                >
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-bold text-primary">{{ receta.nombre }}</h3>
                        <span class="text-2xl">{{ receta.categoria === 'plato' ? '🍽️' : '🥤' }}</span>
                    </div>

                    <p class="text-sm text-secondary mb-4">{{ receta.descripcion }}</p>

                    <div class="space-y-2 mb-4">
                        <p class="text-sm text-secondary" v-if="receta?.tiempo_preparacion">
                            <span class="font-semibold">Tiempo:</span> {{ receta.tiempo_preparacion }} min
                        </p>
                        <p class="text-sm text-secondary">
                            <span class="font-semibold">Insumos:</span> {{ receta.insumos?.length || 0 }}
                        </p>
                        <p class="text-sm text-secondary">
                            <span class="font-semibold">Precio base:</span>
                            <template v-if="obtenerPrecioManual(receta.id) !== null">
                                {{ formatearMoneda(obtenerPrecioManual(receta.id)) }}
                            </template>
                            <template v-else>
                                <span class="text-xs text-secondary ml-1">Defínelo en la edición</span>
                            </template>
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <Link
                            :href="`/recetas/${receta.id}/edit`"
                            class="flex-1 text-center px-3 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors text-sm"
                        >
                            ✏️ Editar
                        </Link>
                        <button
                            @click="deleteReceta(receta.id)"
                            class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm"
                        >
                            🗑️ Eliminar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="recetas.links" class="flex justify-center gap-2">
                <template v-for="(link, index) in recetas.links" :key="index">
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
    </MainLayout>
</template>

<style scoped>
.bg-card {
    background-color: var(--color-sidebar);
    border: 1px solid var(--color-accent);
}
</style>
