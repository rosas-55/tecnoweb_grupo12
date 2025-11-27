<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    estadisticas: Object,
    search: String
});

const searchQuery = ref(props.search || '');

const handleSearch = (query) => {
    router.get('/reportes', { search: query }, {
        preserveState: true,
        preserveScroll: true
    });
};

const fechaInicio = ref('');
const fechaFin = ref('');
const tipoReporte = ref('ventas');

const generarReporte = () => {
    router.get('/reportes/generar', {
        tipo: tipoReporte.value,
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value
    });
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
        title="Reportes" 
        search-placeholder="Buscar en estadísticas..."
        :on-search="handleSearch"
    >
        <div class="space-y-6">
            <!-- Header -->
            <h1 class="text-2xl font-bold text-primary">📈 Centro de Reportes</h1>

            <!-- Filtros -->
            <div class="bg-card rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-primary mb-4">Generar Reporte</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm text-secondary mb-2">Tipo de Reporte</label>
                        <select 
                            v-model="tipoReporte"
                            class="w-full px-3 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30"
                        >
                            <option value="ventas">💰 Ventas</option>
                            <option value="produccion">🏭 Producción</option>
                            <option value="inventario">📦 Inventario</option>
                            <option value="pagos">💳 Pagos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-secondary mb-2">Fecha Inicio</label>
                        <input 
                            type="date" 
                            v-model="fechaInicio"
                            class="w-full px-3 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30"
                        >
                    </div>
                    <div>
                        <label class="block text-sm text-secondary mb-2">Fecha Fin</label>
                        <input 
                            type="date" 
                            v-model="fechaFin"
                            class="w-full px-3 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30"
                        >
                    </div>
                    <div class="flex items-end">
                        <button 
                            @click="generarReporte"
                            class="w-full px-4 py-2 bg-accent text-white rounded-lg hover:bg-accent/80 transition-colors"
                        >
                            📊 Generar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Reportes rápidos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-card rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-primary">Ventas del Día</h3>
                        <span class="text-3xl">💰</span>
                    </div>
                    <p class="text-2xl font-bold text-accent">{{ formatCurrency(estadisticas?.ventas_dia || 0) }}</p>
                </div>

                <div class="bg-card rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-primary">Ventas del Mes</h3>
                        <span class="text-3xl">📊</span>
                    </div>
                    <p class="text-2xl font-bold text-accent">{{ formatCurrency(estadisticas?.ventas_mes || 0) }}</p>
                </div>

                <div class="bg-card rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-primary">Productos Activos</h3>
                        <span class="text-3xl">📝</span>
                    </div>
                    <p class="text-2xl font-bold text-accent">{{ estadisticas?.productos_activos || 0 }}</p>
                </div>

                <div class="bg-card rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-primary">Stock Bajo</h3>
                        <span class="text-3xl">⚠️</span>
                    </div>
                    <p class="text-2xl font-bold text-red-500">{{ estadisticas?.stock_bajo || 0 }}</p>
                </div>
            </div>

            <!-- Historial de reportes -->
            <div class="bg-card rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-primary mb-4">📋 Acciones Rápidas</h3>
                <div class="space-y-3">
                    <p class="text-secondary text-sm">Selecciona un tipo de reporte y fechas para generar un informe detallado.</p>
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
