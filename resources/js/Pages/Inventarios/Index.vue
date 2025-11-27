<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    inventarios: Object,
    search: String
});

const searchQuery = ref(props.search || '');

const handleSearch = (query) => {
    router.get('/inventarios', { search: query }, {
        preserveState: true,
        preserveScroll: true
    });
};

const getUnidadCompleta = (unidad) => {
    const unidades = {
        'kg': 'Kilogramos',
        'g': 'Gramos',
        'l': 'Litros',
        'ml': 'Mililitros',
        'unidad': 'Unidades',
        'und': 'Unidades',
        'pza': 'Piezas',
        'lb': 'Libras',
        'oz': 'Onzas',
        'caja': 'Cajas',
        'paquete': 'Paquetes',
        'docena': 'Docenas'
    };
    return unidades[unidad?.toLowerCase()] || unidad;
};
</script>

<template>
    <MainLayout 
        title="Inventario" 
        search-placeholder="Buscar movimientos por insumo..."
        :on-search="handleSearch"
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-primary">📋 Control de Inventario</h1>
                <Link 
                    href="/inventarios/create"
                    class="px-4 py-2 bg-accent text-white rounded-lg hover:bg-accent/80 transition-colors"
                >
                    ➕ Registrar Movimiento
                </Link>
            </div>

            <!-- Tabla de inventario -->
            <div class="bg-card rounded-lg shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-accent/10">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Insumo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Tipo Movimiento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Observación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-accent/20">
                        <tr v-for="inv in inventarios.data" :key="inv.id" class="hover:bg-accent/5">
                            <td class="px-6 py-4 text-sm text-secondary whitespace-nowrap">
                                {{ inv.fecha.substring(0, 10).split('-').reverse().join('/') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-primary font-medium">
                                {{ inv.insumo?.nombre }}
                                <span class="text-xs text-secondary block">{{ getUnidadCompleta(inv.insumo?.unidad_medida) }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="[
                                    'px-3 py-1 rounded-full text-xs font-semibold',
                                    inv.tipo_movimiento === 'ingreso' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                                ]">
                                    {{ inv.tipo_movimiento === 'ingreso' ? '📥 Ingreso' : '📤 Salida' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold" :class="inv.tipo_movimiento === 'ingreso' ? 'text-green-600' : 'text-red-600'">
                                {{ inv.tipo_movimiento === 'ingreso' ? '+' : '-' }}{{ inv.cantidad }} {{ getUnidadCompleta(inv.insumo?.unidad_medida) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-secondary">
                                {{ inv.observacion || 'Sin observación' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="inventarios.links" class="flex justify-center gap-2">
                <template v-for="(link, index) in inventarios.links" :key="index">
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
