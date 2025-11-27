<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    pagos: Object,
    search: String
});

const searchQuery = ref(props.search || '');

const handleSearch = (query) => {
    router.get('/pagos', { search: query }, {
        preserveState: true,
        preserveScroll: true
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2
    }).format(value || 0);
};

const formatDate = (value) => {
    if (!value) {
        return 'Sin fecha';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString('es-BO', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <MainLayout
        title="Pagos"
        search-placeholder="Buscar pagos por venta o método..."
        :on-search="handleSearch"
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-primary">💳 Gestión de Pagos</h1>
                <Link
                    href="/pagos/create"
                    class="px-4 py-2 bg-accent text-white rounded-lg hover:bg-accent/80 transition-colors"
                >
                    ➕ Registrar Pago
                </Link>
            </div>

            <!-- Tabla de pagos -->
            <div class="bg-card rounded-lg shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-accent/10">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Venta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-accent/20">
                        <tr v-for="pago in pagos.data" :key="pago.id" class="hover:bg-accent/5">
                            <td class="px-6 py-4 text-sm text-primary">{{ pago.id }}</td>
                            <td class="px-6 py-4 text-sm text-secondary">
                                {{ formatDate(pago.fechapago || pago.created_at) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-primary">Venta #{{ pago.venta_id }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    pago.metodo_pago === 1 ? 'bg-green-500 text-white' :
                                    pago.metodo_pago === 2 ? 'bg-blue-500 text-white' :
                                    pago.metodo_pago === 5 ? 'bg-purple-500 text-white' :
                                    'bg-gray-500 text-white'
                                ]">
                                    {{ pago.metodo_pago === 1 ? '💵 Efectivo' :
                                       pago.metodo_pago === 2 ? '💳 Tarjeta' :
                                       pago.metodo_pago === 5 ? '📱 QR Code' :
                                       '📱 Transferencia' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-primary font-bold">{{ formatCurrency(pago.monto) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    pago.estado === 1 ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                                ]">
                                    {{ pago.estado === 1 ? '✅ Completado' : '⏳ Pendiente' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <Link
                                    :href="`/pagos/${pago.id}`"
                                    :class="[
                                        'px-3 py-1 rounded-lg transition-colors',
                                        pago.metodo_pago === 5
                                            ? 'bg-purple-500 text-white hover:bg-purple-600'
                                            : 'text-blue-500 hover:text-blue-700'
                                    ]"
                                >
                                    {{ pago.metodo_pago === 5 ? '📱 Ver QR' : '👁️ Ver' }}
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="pagos.links" class="flex justify-center gap-2">
                <template v-for="(link, index) in pagos.links" :key="index">
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
