<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    venta: Object
});

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const fechaSolo = fecha.substring(0, 10);
    const [year, month, day] = fechaSolo.split('-');
    const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    return `${parseInt(day)} de ${meses[parseInt(month) - 1]} de ${year}`;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <MainLayout title="Detalles de Venta">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header con Badge de ID -->
            <div class="bg-linear-to-r from-accent to-accent/80 rounded-2xl shadow-2xl p-8">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                            <span class="text-5xl">🧾</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-3xl font-bold text-white">Venta</h1>
                                <span class="bg-white/30 backdrop-blur-sm px-4 py-1.5 rounded-full text-white font-bold text-lg">
                                    #{{ String(venta.id).padStart(4, '0') }}
                                </span>
                            </div>
                            <p class="text-white/90 mt-1 text-lg">{{ formatearFecha(venta.fecha) }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <Link 
                            :href="`/ventas/${venta.id}/edit`"
                            class="px-5 py-3 bg-white text-accent rounded-xl hover:bg-white/90 transition-all shadow-lg hover:shadow-xl font-semibold flex items-center gap-2"
                        >
                            <span class="text-xl">✏️</span> Editar
                        </Link>
                        <Link 
                            href="/ventas"
                            class="px-5 py-3 bg-white/20 backdrop-blur-sm text-white rounded-xl hover:bg-white/30 transition-all font-semibold flex items-center gap-2"
                        >
                            <span class="text-xl">←</span> Volver
                        </Link>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Información del Cliente -->
                <div class="bg-card rounded-2xl shadow-xl p-6 border-2 border-accent/20">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-linear-to-br from-blue-500 to-blue-600 rounded-xl p-3">
                            <span class="text-3xl">👤</span>
                        </div>
                        <h2 class="text-xl font-bold text-primary">Cliente</h2>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-linear-to-br from-accent to-accent/70 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                            {{ venta.cliente?.name?.charAt(0).toUpperCase() || '👥' }}
                        </div>
                        <div>
                            <p class="text-primary font-bold text-lg">{{ venta.cliente?.name || 'Cliente General' }}</p>
                            <p v-if="venta.cliente?.email" class="text-secondary text-sm">{{ venta.cliente.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Información del Vendedor -->
                <div class="bg-card rounded-2xl shadow-xl p-6 border-2 border-accent/20">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-linear-to-br from-purple-500 to-purple-600 rounded-xl p-3">
                            <span class="text-3xl">👨‍💼</span>
                        </div>
                        <h2 class="text-xl font-bold text-primary">Vendedor</h2>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-linear-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                            {{ venta.usuario?.name?.charAt(0).toUpperCase() || '?' }}
                        </div>
                        <div>
                            <p class="text-primary font-bold text-lg">{{ venta.usuario?.name || 'Sin asignar' }}</p>
                            <p v-if="venta.usuario?.email" class="text-secondary text-sm">{{ venta.usuario.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Estados -->
                <div class="bg-card rounded-2xl shadow-xl p-6 border-2 border-accent/20">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-linear-to-br from-green-500 to-green-600 rounded-xl p-3">
                            <span class="text-3xl">📊</span>
                        </div>
                        <h2 class="text-xl font-bold text-primary">Estado</h2>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <p class="text-secondary text-xs mb-1">Tipo de Venta</p>
                            <span :class="[
                                'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold shadow-md w-full justify-center',
                                venta.tipo === 1 ? 'bg-linear-to-r from-green-500 to-green-600 text-white' : 'bg-linear-to-r from-amber-500 to-amber-600 text-white'
                            ]">
                                <span class="text-xl">{{ venta.tipo === 1 ? '💵' : '📋' }}</span>
                                {{ venta.tipo === 1 ? 'Contado' : 'Crédito' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-secondary text-xs mb-1">Estado de Pago</p>
                            <span :class="[
                                'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold shadow-md w-full justify-center',
                                venta.estado === 1 ? 'bg-linear-to-r from-green-500 to-green-600 text-white' : 'bg-linear-to-r from-red-500 to-red-600 text-white'
                            ]">
                                <span class="text-xl">{{ venta.estado === 1 ? '✅' : '⏳' }}</span>
                                {{ venta.estado === 1 ? 'Pagado' : 'Pendiente' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos Vendidos -->
            <div class="bg-card rounded-2xl shadow-xl overflow-hidden border-2 border-accent/20">
                <div class="bg-linear-to-r from-accent to-accent/80 px-8 py-5">
                    <div class="flex items-center gap-3">
                        <span class="text-4xl">🍽️</span>
                        <h2 class="text-2xl font-bold text-white">Productos Vendidos</h2>
                        <span v-if="venta.detalle_ventas && venta.detalle_ventas.length > 0" 
                              class="ml-auto bg-white/30 backdrop-blur-sm px-4 py-1 rounded-full text-white font-bold">
                            {{ venta.detalle_ventas.length }} producto{{ venta.detalle_ventas.length !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                
                <div v-if="!venta.detalle_ventas || venta.detalle_ventas.length === 0" class="p-12 text-center">
                    <div class="text-8xl mb-4 opacity-50">📦</div>
                    <p class="text-lg text-secondary font-medium">No hay productos en esta venta</p>
                </div>

                <div v-else class="p-6">
                    <div class="space-y-4">
                        <div v-for="(detalle, index) in venta.detalle_ventas" :key="detalle.id" 
                             class="bg-accent/5 rounded-xl p-5 hover:bg-accent/10 transition-all border border-accent/20">
                            <div class="flex items-start gap-4">
                                <div class="shrink-0">
                                    <div class="w-16 h-16 rounded-full bg-linear-to-br from-accent to-accent/70 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                        {{ index + 1 }}
                                    </div>
                                </div>
                                <div class="grow">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="text-xl font-bold text-primary">{{ detalle.receta?.nombre || 'Producto' }}</h3>
                                            <p v-if="detalle.receta?.descripcion" class="text-secondary text-sm mt-1">
                                                {{ detalle.receta.descripcion }}
                                            </p>
                                            <p v-if="detalle.receta?.tiempo_preparacion" class="text-xs text-secondary mt-1 flex items-center gap-1">
                                                <span>⏱️</span> Preparación: {{ detalle.receta.tiempo_preparacion }} minutos
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-3xl font-bold text-accent">{{ formatCurrency(detalle.subtotal) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-6 mt-3">
                                        <div class="bg-white/50 rounded-lg px-4 py-2">
                                            <p class="text-xs text-secondary">Cantidad</p>
                                            <p class="text-lg font-bold text-primary">{{ detalle.cantidad }}</p>
                                        </div>
                                        <div class="bg-white/50 rounded-lg px-4 py-2">
                                            <p class="text-xs text-secondary">Precio Unit.</p>
                                            <p class="text-lg font-bold text-primary">{{ formatCurrency(detalle.precio_unitario) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="bg-linear-to-r from-accent/20 to-accent/10 px-8 py-6 border-t-2 border-accent/30">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="text-4xl">💰</span>
                            <span class="text-2xl font-bold text-primary">TOTAL DE LA VENTA</span>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-accent">{{ formatCurrency(venta.total) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagos Realizados -->
            <div v-if="venta.pagos && venta.pagos.length > 0" class="bg-card rounded-2xl shadow-xl overflow-hidden border-2 border-accent/20">
                <div class="bg-linear-to-r from-green-500 to-green-600 px-8 py-5">
                    <div class="flex items-center gap-3">
                        <span class="text-4xl">💳</span>
                        <h2 class="text-2xl font-bold text-white">Pagos Realizados</h2>
                        <span class="ml-auto bg-white/30 backdrop-blur-sm px-4 py-1 rounded-full text-white font-bold">
                            {{ venta.pagos.length }} pago{{ venta.pagos.length !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                <div class="p-6 space-y-3">
                    <div v-for="pago in venta.pagos" :key="pago.id" 
                         class="bg-green-50 rounded-xl p-5 border-2 border-green-200 hover:border-green-300 transition-all">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-linear-to-br from-green-500 to-green-600 flex items-center justify-center text-white text-xl">
                                    💵
                                </div>
                                <div>
                                    <p class="text-primary font-bold text-lg">{{ formatearFecha(pago.fecha) }}</p>
                                    <p class="text-secondary text-sm">Método: <span class="font-semibold">{{ pago.metodo_pago }}</span></p>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(pago.monto) }}</p>
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
}
</style>
