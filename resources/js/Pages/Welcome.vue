<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="flex items-center justify-center min-h-screen">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Bienvenido a Restaurante Brazas
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Aplicación configurada con Laravel + Inertia.js + Vue 3
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

