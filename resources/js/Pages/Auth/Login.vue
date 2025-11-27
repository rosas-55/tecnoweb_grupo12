<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-50 via-amber-50 to-red-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gradient-to-br from-orange-500 to-red-600 shadow-lg mb-4">
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h2 class="text-4xl font-bold text-gray-800 mb-2">
                    Bienvenido
                </h2>
                <h3 class="text-2xl font-semibold text-orange-600 mb-2">
                    Restaurante las Brazas
                </h3>
                <p class="text-gray-600">
                    Inicia sesión para continuar
                </p>
            </div>
            <form class="mt-8 space-y-6 bg-white p-8 rounded-2xl shadow-xl" @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Correo electrónico
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                class="block w-full pl-10 pr-3 py-3 border border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 placeholder-gray-400 transition"
                                placeholder="tu@email.com"
                            />
                        </div>
                        <div v-if="errors.email" class="mt-1 text-sm text-red-600">
                            {{ errors.email }}
                        </div>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="block w-full pl-10 pr-3 py-3 border border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 placeholder-gray-400 transition"
                                placeholder="••••••••"
                            />
                        </div>
                        <div v-if="errors.password" class="mt-1 text-sm text-red-600">
                            {{ errors.password }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                    <input
                        id="remember-me"
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-orange-300 rounded"
                    />
                    <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                        Recordarme
                    </label>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-base font-semibold rounded-lg text-white bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 shadow-lg transform transition hover:scale-105"
                    >
                        <span v-if="processing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Iniciando sesión...
                        </span>
                        <span v-else class="flex items-center">
                            Iniciar Sesión
                            <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </button>
                </div>

                <div class="text-center pt-4 border-t border-orange-100">
                    <p class="text-sm text-gray-600">
                        ¿No tienes cuenta?
                        <Link
                            href="/register"
                            class="font-semibold text-orange-600 hover:text-orange-700 transition"
                        >
                            Regístrate aquí
                        </Link>
                    </p>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';

defineProps({
    errors: Object,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const processing = form.processing;

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

