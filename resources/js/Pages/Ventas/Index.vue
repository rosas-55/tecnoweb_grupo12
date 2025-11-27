<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    ventas: Object,
    search: String
});

const searchQuery = ref(props.search || '');

const handleSearch = (query) => {
    router.get('/ventas', { search: query }, {
        preserveState: true,
        preserveScroll: true
    });
};

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    // Extraer solo la fecha sin conversión de zona horaria
    const fechaSolo = fecha.substring(0, 10);
    const [year, month, day] = fechaSolo.split('-');
    const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    return `${parseInt(day)} de ${meses[parseInt(month) - 1]} de ${year},00:00`;
};

const deleteVenta = (id) => {
    if (confirm('¿Está seguro de eliminar esta venta?')) {
        router.delete(`/ventas/${id}`);
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP'
    }).format(value);
};
</script>

<template>
    <MainLayout 
        title="Ventas" 
        search-placeholder="Buscar ventas por cliente o monto..."
        :on-search="handleSearch"
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-primary">💰 Gestión de Ventas</h1>
                <Link 
                    href="/ventas/create"
                    class="px-4 py-2 bg-accent text-white rounded-lg hover:bg-accent/80 transition-colors"
                >
                    ➕ Nueva Venta
                </Link>
            </div>

            <!-- Tabla de ventas -->
            <div class="bg-card rounded-lg shadow-lg overflow-hidden">
                <div v-if="ventas.data.length === 0" class="p-8 text-center text-secondary">
                    <div class="text-6xl mb-4">📋</div>
                    <p class="text-lg">No hay ventas registradas</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-accent text-white">
                            <tr>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">#</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">📅 Fecha</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">👤 Cliente</th>
                                <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider">💳 Tipo</th>
                                <th class="px-4 py-4 text-right text-xs font-bold uppercase tracking-wider">💵 Total</th>
                                <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider">🟢 Estado</th>
                                <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider">⚙️ Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-accent/20">
                            <tr v-for="venta in ventas.data" :key="venta.id" class="hover:bg-accent/5 transition-colors">
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-accent/20 text-accent font-bold text-sm">
                                        {{ venta.id }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm">
                                        <div class="text-primary font-medium">{{ formatearFecha(venta.fecha).split(',')[0] }}</div>
                                        <div class="text-secondary text-xs">{{ formatearFecha(venta.fecha).split(',')[1] }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-9 h-9 rounded-full bg-accent/40 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                            {{ venta.cliente?.name?.charAt(0).toUpperCase() || '👥' }}
                                        </div>
                                        <div>
                                            <div class="text-primary font-semibold text-sm">{{ venta.cliente?.name || 'Cliente General' }}</div>
                                            <div v-if="venta.cliente?.email" class="text-secondary text-xs">{{ venta.cliente.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span :class="[
                                        'inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold shadow-sm',
                                        venta.tipo === 1 ? 'bg-green-500 text-white' : 'bg-amber-500 text-white'
                                    ]">
                                        <span class="text-base">{{ venta.tipo === 1 ? '💵' : '📋' }}</span>
                                        {{ venta.tipo === 1 ? 'Contado' : 'Crédito' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="text-primary font-bold text-lg">{{ formatCurrency(venta.total) }}</div>
                                    <div v-if="venta.detalles && venta.detalles.length" class="text-secondary text-xs">
                                        {{ venta.detalles.length }} producto{{ venta.detalles.length !== 1 ? 's' : '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span :class="[
                                        'inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold shadow-sm',
                                        venta.estado === 1 ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                                    ]">
                                        <span class="text-base">{{ venta.estado === 1 ? '✅' : '⏳' }}</span>
                                        {{ venta.estado === 1 ? 'Pagado' : 'Pendiente' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <Link 
                                            :href="`/ventas/${venta.id}`"
                                            class="px-3 py-2 bg-blue-500/20 text-blue-600 rounded-lg hover:bg-blue-500/30 transition-colors font-medium text-sm"
                                            title="Ver detalles"
                                        >
                                            👁️ Ver
                                        </Link>
                                        <Link 
                                            :href="`/ventas/${venta.id}/edit`"
                                            class="px-3 py-2 bg-amber-500/20 text-amber-600 rounded-lg hover:bg-amber-500/30 transition-colors font-medium text-sm"
                                            title="Editar venta"
                                        >
                                            ✏️ Editar
                                        </Link>
                                        <button 
                                            @click="deleteVenta(venta.id)"
                                            class="px-3 py-2 bg-red-500/20 text-red-600 rounded-lg hover:bg-red-500/30 transition-colors font-medium text-sm"
                                            title="Eliminar venta"
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
            <div v-if="ventas.links" class="flex justify-center gap-2">
                <template v-for="(link, index) in ventas.links" :key="index">
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
