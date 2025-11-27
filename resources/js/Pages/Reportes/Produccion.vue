<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    reporte: Object,
    filtros: Object
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <MainLayout title="Reporte de Producción">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-primary">🏭 Reporte de Producción</h1>
                <a 
                    href="/reportes"
                    class="px-4 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition-colors"
                >
                    ← Volver
                </a>
            </div>

            <!-- Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-card rounded-lg shadow-lg p-6">
                    <h3 class="text-sm text-secondary mb-2">Total Producciones</h3>
                    <p class="text-2xl font-bold text-accent">{{ reporte.producciones.length }}</p>
                </div>
                <div class="bg-card rounded-lg shadow-lg p-6">
                    <h3 class="text-sm text-secondary mb-2">Unidades Producidas</h3>
                    <p class="text-2xl font-bold text-accent">{{ reporte.total_producciones }}</p>
                </div>
            </div>

            <!-- Recetas más producidas -->
            <div class="bg-card rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-primary mb-4">Top 10 Recetas Más Producidas</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-accent/20">
                            <tr>
                                <th class="px-4 py-3 text-left text-primary">#</th>
                                <th class="px-4 py-3 text-left text-primary">Receta</th>
                                <th class="px-4 py-3 text-right text-primary">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(receta, index) in reporte.recetas_mas_producidas" 
                                :key="index"
                                class="border-t border-accent/10"
                            >
                                <td class="px-4 py-3 text-secondary">{{ index + 1 }}</td>
                                <td class="px-4 py-3 text-primary">{{ receta.receta }}</td>
                                <td class="px-4 py-3 text-right text-primary font-semibold">{{ receta.cantidad }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detalle de producciones -->
            <div class="bg-card rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-primary mb-4">Detalle de Producciones</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-accent/20">
                            <tr>
                                <th class="px-4 py-3 text-left text-primary">Fecha</th>
                                <th class="px-4 py-3 text-left text-primary">Receta</th>
                                <th class="px-4 py-3 text-right text-primary">Cantidad</th>
                                <th class="px-4 py-3 text-left text-primary">Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="prod in reporte.producciones" 
                                :key="prod.id"
                                class="border-t border-accent/10"
                            >
                                <td class="px-4 py-3 text-secondary">{{ formatDate(prod.fecha) }}</td>
                                <td class="px-4 py-3 text-primary">{{ prod.receta.nombre }}</td>
                                <td class="px-4 py-3 text-right text-primary font-semibold">{{ prod.cantidad_producida }}</td>
                                <td class="px-4 py-3 text-secondary">{{ prod.responsable.name }}</td>
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
