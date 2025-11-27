<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    reporte: Object
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP'
    }).format(value);
};
</script>

<template>
    <MainLayout title="Reporte de Inventario">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-primary">📦 Reporte de Inventario</h1>
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
                    <h3 class="text-sm text-secondary mb-2">Valor Total Inventario</h3>
                    <p class="text-2xl font-bold text-accent">{{ formatCurrency(reporte.valor_total) }}</p>
                </div>
                <div class="bg-card rounded-lg shadow-lg p-6">
                    <h3 class="text-sm text-secondary mb-2">Insumos con Stock Bajo</h3>
                    <p class="text-2xl font-bold text-yellow-500">{{ reporte.stock_bajo.length }}</p>
                </div>
                <div class="bg-card rounded-lg shadow-lg p-6">
                    <h3 class="text-sm text-secondary mb-2">Insumos Agotados</h3>
                    <p class="text-2xl font-bold text-red-500">{{ reporte.stock_critico.length }}</p>
                </div>
            </div>

            <!-- Listado de insumos -->
            <div class="bg-card rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-primary mb-4">Listado de Insumos</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-accent/20">
                            <tr>
                                <th class="px-4 py-3 text-left text-primary">Insumo</th>
                                <th class="px-4 py-3 text-right text-primary">Stock Actual</th>
                                <th class="px-4 py-3 text-right text-primary">Stock Mínimo</th>
                                <th class="px-4 py-3 text-right text-primary">Precio Unit.</th>
                                <th class="px-4 py-3 text-right text-primary">Valor Total</th>
                                <th class="px-4 py-3 text-center text-primary">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="insumo in reporte.insumos" 
                                :key="insumo.id"
                                class="border-t border-accent/10"
                            >
                                <td class="px-4 py-3 text-primary">{{ insumo.nombre }}</td>
                                <td class="px-4 py-3 text-right text-primary">{{ insumo.stock_actual }} {{ insumo.unidad_medida }}</td>
                                <td class="px-4 py-3 text-right text-secondary">{{ insumo.stock_minimo }} {{ insumo.unidad_medida }}</td>
                                <td class="px-4 py-3 text-right text-secondary">{{ formatCurrency(insumo.precio_unitario) }}</td>
                                <td class="px-4 py-3 text-right text-primary font-semibold">{{ formatCurrency(insumo.stock_actual * insumo.precio_unitario) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span 
                                        v-if="insumo.stock_actual === 0"
                                        class="px-2 py-1 text-xs rounded-full bg-red-500 text-white"
                                    >
                                        Agotado
                                    </span>
                                    <span 
                                        v-else-if="insumo.stock_actual <= insumo.stock_minimo"
                                        class="px-2 py-1 text-xs rounded-full bg-yellow-500 text-white"
                                    >
                                        Bajo
                                    </span>
                                    <span 
                                        v-else
                                        class="px-2 py-1 text-xs rounded-full bg-green-500 text-white"
                                    >
                                        Normal
                                    </span>
                                </td>
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
