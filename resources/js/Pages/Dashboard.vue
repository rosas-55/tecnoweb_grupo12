<script setup>
import { computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    widgets: Object,
    graficas: Object,
    pageVisits: Object
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP'
    }).format(value);
};
</script>

<template>
    <MainLayout title="Dashboard" search-placeholder="Buscar en dashboard...">
        <div class="space-y-6">
            <!-- Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                <div 
                    v-for="(widget, key) in widgets" 
                    :key="key"
                    :class="[
                        'p-6 rounded-lg shadow-lg',
                        `bg-${widget.color}-500 text-white`
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">{{ widget.label }}</p>
                            <p class="text-2xl font-bold mt-2">
                                {{ key.includes('ventas') && !key.includes('pendientes') 
                                    ? formatCurrency(widget.value) 
                                    : widget.value }}
                            </p>
                        </div>
                        <span class="text-4xl">{{ widget.icon }}</span>
                    </div>
                </div>
            </div>

            <!-- Gráficas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Ventas últimos 7 días -->
                <div class="bg-card p-6 rounded-lg shadow-lg">
                    <h3 class="text-lg font-bold text-primary mb-4">📈 Ventas Últimos 7 Días</h3>
                    <div class="space-y-2">
                        <div 
                            v-for="dia in graficas.ventas_ultimos_7_dias" 
                            :key="dia.fecha"
                            class="flex items-center gap-2"
                        >
                            <span class="text-sm text-secondary w-16">{{ dia.fecha }}</span>
                            <div class="flex-1 bg-accent/20 rounded-full h-6 overflow-hidden">
                                <div 
                                    class="bg-accent h-full flex items-center justify-end pr-2 text-xs text-white font-semibold"
                                    :style="{ width: `${(dia.total / Math.max(...graficas.ventas_ultimos_7_dias.map(d => d.total))) * 100}%` }"
                                >
                                    {{ formatCurrency(dia.total) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ventas por tipo -->
                <div class="bg-card p-6 rounded-lg shadow-lg">
                    <h3 class="text-lg font-bold text-primary mb-4">💳 Ventas por Tipo (Este Mes)</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-secondary">Contado</span>
                                <span class="font-semibold text-primary">{{ formatCurrency(graficas.ventas_por_tipo.contado) }}</span>
                            </div>
                            <div class="w-full bg-accent/20 rounded-full h-4">
                                <div 
                                    class="bg-green-500 h-4 rounded-full"
                                    :style="{ width: `${(graficas.ventas_por_tipo.contado / (graficas.ventas_por_tipo.contado + graficas.ventas_por_tipo.credito)) * 100}%` }"
                                ></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-secondary">Crédito</span>
                                <span class="font-semibold text-primary">{{ formatCurrency(graficas.ventas_por_tipo.credito) }}</span>
                            </div>
                            <div class="w-full bg-accent/20 rounded-full h-4">
                                <div 
                                    class="bg-yellow-500 h-4 rounded-full"
                                    :style="{ width: `${(graficas.ventas_por_tipo.credito / (graficas.ventas_por_tipo.contado + graficas.ventas_por_tipo.credito)) * 100}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos más vendidos -->
                <div class="bg-card p-6 rounded-lg shadow-lg">
                    <h3 class="text-lg font-bold text-primary mb-4">🔥 Productos Más Vendidos</h3>
                    <div class="space-y-3">
                        <div 
                            v-for="(producto, index) in graficas.productos_mas_vendidos" 
                            :key="index"
                            class="flex items-center gap-3"
                        >
                            <span class="text-2xl font-bold text-accent">{{ index + 1 }}</span>
                            <div class="flex-1">
                                <p class="font-semibold text-primary">{{ producto.nombre }}</p>
                                <p class="text-sm text-secondary">{{ producto.cantidad }} unidades</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contador de visitas -->
                <div class="bg-card p-6 rounded-lg shadow-lg">
                    <h3 class="text-lg font-bold text-primary mb-4">👀 Páginas Más Visitadas</h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <div 
                            v-for="(visitas, path) in pageVisits" 
                            :key="path"
                            class="flex justify-between items-center p-2 hover:bg-accent/10 rounded"
                        >
                            <span class="text-sm text-secondary truncate">{{ path }}</span>
                            <span class="font-semibold text-primary ml-2">{{ visitas }}</span>
                        </div>
                    </div>
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

