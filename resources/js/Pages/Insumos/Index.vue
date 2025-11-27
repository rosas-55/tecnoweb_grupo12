<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/Helpers/permissions';

const props = defineProps({
    insumos: Object,
    search: String
});

const { hasPermission } = usePermissions();
const searchQuery = ref(props.search || '');

const handleSearch = (query) => {
    router.get('/insumos', { search: query }, {
        preserveState: true,
        preserveScroll: true
    });
};

const deleteInsumo = (id) => {
    if (confirm('¿Está seguro de eliminar este insumo?')) {
        router.delete(`/insumos/${id}`);
    }
};
</script>

<template>
    <MainLayout
        title="Insumos"
        search-placeholder="Buscar insumos por nombre..."
        :on-search="handleSearch"
    >
        <div class="space-y-6">
            <!-- Header con botón de crear -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-primary">📦 Gestión de Insumos</h1>
                <Link
                    v-if="hasPermission('insumos.create')"
                    href="/insumos/create"
                    class="px-4 py-2 bg-accent text-white rounded-lg hover:bg-accent/80 transition-colors"
                >
                    ➕ Nuevo Insumo
                </Link>
            </div>

            <!-- Tabla de insumos -->
            <div class="bg-card rounded-lg shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-accent/10">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Unidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Stock Actual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Stock Mínimo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-accent/20">
                        <tr v-for="insumo in insumos.data" :key="insumo.id" class="hover:bg-accent/5">
                            <td class="px-6 py-4 text-sm text-primary">{{ insumo.id }}</td>
                            <td class="px-6 py-4 text-sm text-primary font-medium">{{ insumo.nombre }}</td>
                            <td class="px-6 py-4 text-sm text-secondary">{{ insumo.unidad_medida }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    insumo.stock_actual <= insumo.stock_minimo
                                        ? 'bg-red-500 text-white'
                                        : 'bg-green-500 text-white'
                                ]">
                                    {{ insumo.stock_actual }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-secondary">{{ insumo.stock_minimo }}</td>
                            <td class="px-6 py-4 text-sm text-primary">${{ insumo.costo_unitario }}</td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <Link
                                    v-if="hasPermission('insumos.update')"
                                    :href="`/insumos/${insumo.id}/edit`"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    ✏️ Editar
                                </Link>
                                <button
                                    v-if="hasPermission('insumos.delete')"
                                    @click="deleteInsumo(insumo.id)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    🗑️ Eliminar
                                </button>
                                <span v-if="!hasPermission('insumos.update') && !hasPermission('insumos.delete')" class="text-secondary text-xs">
                                    Sin permisos
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="insumos.links" class="flex justify-center gap-2">
                <template v-for="(link, index) in insumos.links" :key="index">
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
