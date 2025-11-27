<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-50 via-amber-50 to-red-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gradient-to-br from-orange-500 to-red-600 shadow-lg mb-4">
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h2 class="text-4xl font-bold text-gray-800 mb-2">
                    Únete a Nosotros
                </h2>
                <h3 class="text-2xl font-semibold text-orange-600 mb-2">
                    Restaurante las Brazas
                </h3>
                <p class="text-gray-600">
                    Crea tu cuenta y disfruta de nuestros servicios
                </p>
            </div>
            <form class="mt-8 space-y-6 bg-white p-8 rounded-2xl shadow-xl" @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Nombre completo
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full px-4 py-3 border border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 placeholder-gray-400 transition"
                            placeholder="Nombre completo"
                        />
                        <div v-if="errors.name" class="mt-1 text-sm text-red-600">
                            {{ errors.name }}
                        </div>
                    </div>

                    <div>
                        <label for="cedula" class="block text-sm font-medium text-gray-700">
                            Cédula
                        </label>
                        <input
                            id="cedula"
                            v-model="form.cedula"
                            type="text"
                            class="mt-1 block w-full px-4 py-3 border border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 placeholder-gray-400 transition"
                            placeholder="Cédula (opcional)"
                        />
                        <div v-if="errors.cedula" class="mt-1 text-sm text-red-600">
                            {{ errors.cedula }}
                        </div>
                    </div>

                    <div>
                        <label for="celular" class="block text-sm font-medium text-gray-700">
                            Celular
                        </label>
                        <input
                            id="celular"
                            v-model="form.celular"
                            type="text"
                            class="mt-1 block w-full px-4 py-3 border border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 placeholder-gray-400 transition"
                            placeholder="Celular (opcional)"
                        />
                        <div v-if="errors.celular" class="mt-1 text-sm text-red-600">
                            {{ errors.celular }}
                        </div>
                    </div>

                    <div>
                        <label for="direccion" class="block text-sm font-medium text-gray-700">
                            Dirección
                        </label>
                        <input
                            id="direccion"
                            v-model="form.direccion"
                            type="text"
                            class="mt-1 block w-full px-4 py-3 border border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 placeholder-gray-400 transition"
                            placeholder="Dirección (opcional)"
                        />
                        <div v-if="errors.direccion" class="mt-1 text-sm text-red-600">
                            {{ errors.direccion }}
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Correo electrónico
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="mt-1 block w-full px-4 py-3 border border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 placeholder-gray-400 transition"
                            placeholder="Correo electrónico"
                        />
                        <div v-if="errors.email" class="mt-1 text-sm text-red-600">
                            {{ errors.email }}
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="mt-1 block w-full px-4 py-3 border border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 placeholder-gray-400 transition"
                            placeholder="Contraseña (mínimo 8 caracteres)"
                        />
                        <div v-if="errors.password" class="mt-1 text-sm text-red-600">
                            {{ errors.password }}
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirmar contraseña
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="mt-1 block w-full px-4 py-3 border border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 placeholder-gray-400 transition"
                            placeholder="Confirmar contraseña"
                        />
                    </div>
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
                            Registrando...
                        </span>
                        <span v-else class="flex items-center">
                            Crear Cuenta
                            <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </button>
                </div>

                <div class="text-center pt-4 border-t border-orange-100">
                    <p class="text-sm text-gray-600">
                        ¿Ya tienes cuenta?
                        <Link
                            href="/login"
                            class="font-semibold text-orange-600 hover:text-orange-700 transition"
                        >
                            Inicia sesión aquí
                        </Link>
                    </p>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

defineProps({
    errors: Object,
});

const form = useForm({
    name: '',
    cedula: '',
    celular: '',
    direccion: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const processing = form.processing;

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

