<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    reporte: Object,
    filtros: Object
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP'
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <MainLayout title="Reporte de Ventas">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-primary">📊 Reporte de Ventas</h1>
                <a 
                    href="/reportes"
                    class="px-4 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition-colors"
                >
                    ← Volver
                </a>
            </div>

            <!-- Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-card rounded-lg shadow-lg p-6">
                    <h3 class="text-sm text-secondary mb-2">Total Ventas</h3>
                    <p class="text-2xl font-bold text-accent">{{ formatCurrency(reporte.total_ventas) }}</p>
                </div>
                <div class="bg-card rounded-lg shadow-lg p-6">
                    <h3 class="text-sm text-secondary mb-2">Promedio por Venta</h3>
                    <p class="text-2xl font-bold text-accent">{{ formatCurrency(reporte.promedio_venta) }}</p>
                </div>
                <div class="bg-card rounded-lg shadow-lg p-6">
                    <h3 class="text-sm text-secondary mb-2">Total Ventas</h3>
                    <p class="text-2xl font-bold text-accent">{{ reporte.ventas.length }}</p>
                </div>
            </div>

            <!-- Por tipo de pago -->
            <div class="bg-card rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-primary mb-4">Por Tipo de Pago</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-accent/5 rounded-lg">
                        <p class="text-sm text-secondary">Contado</p>
                        <p class="text-xl font-bold text-primary">{{ formatCurrency(reporte.ventas_por_tipo.contado) }}</p>
                    </div>
                    <div class="p-4 bg-accent/5 rounded-lg">
                        <p class="text-sm text-secondary">Crédito</p>
                        <p class="text-xl font-bold text-primary">{{ formatCurrency(reporte.ventas_por_tipo.credito) }}</p>
                    </div>
                </div>
            </div>

            <!-- Productos más vendidos -->
            <div class="bg-card rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-primary mb-4">Top 10 Productos Más Vendidos</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-accent/20">
                            <tr>
                                <th class="px-4 py-3 text-left text-primary">#</th>
                                <th class="px-4 py-3 text-left text-primary">Producto</th>
                                <th class="px-4 py-3 text-right text-primary">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(producto, index) in reporte.productos_mas_vendidos" 
                                :key="index"
                                class="border-t border-accent/10"
                            >
                                <td class="px-4 py-3 text-secondary">{{ index + 1 }}</td>
                                <td class="px-4 py-3 text-primary">{{ producto.nombre }}</td>
                                <td class="px-4 py-3 text-right text-primary font-semibold">{{ producto.total_vendido }}</td>
                            </tr>
                        </tbody>
                    </table>
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
