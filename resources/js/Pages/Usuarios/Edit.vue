<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    usuario: Object,
    roles: Array
});

const form = useForm({
    name: props.usuario.name,
    cedula: props.usuario.cedula,
    celular: props.usuario.celular || '',
    direccion: props.usuario.direccion || '',
    email: props.usuario.email,
    password: '',
    password_confirmation: '',
    roles: props.usuario.roles?.map(r => r.id) || []
});

const submit = () => {
    form.put(`/usuarios/${props.usuario.id}`);
};
</script>

<template>
    <MainLayout title="Editar Usuario">
        <div class="max-w-3xl mx-auto">
            <div class="bg-card rounded-lg shadow-lg p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Nombre Completo *</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                        >
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Cédula -->
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Cédula *</label>
                        <input 
                            v-model="form.cedula" 
                            type="text" 
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                        >
                        <p v-if="form.errors.cedula" class="text-red-500 text-sm mt-1">{{ form.errors.cedula }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Correo Electrónico *</label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                            required
                        >
                        <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Celular -->
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Celular</label>
                        <input 
                            v-model="form.celular" 
                            type="text" 
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                        >
                        <p v-if="form.errors.celular" class="text-red-500 text-sm mt-1">{{ form.errors.celular }}</p>
                    </div>

                    <!-- Dirección -->
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Dirección</label>
                        <textarea 
                            v-model="form.direccion" 
                            rows="3"
                            class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                        ></textarea>
                        <p v-if="form.errors.direccion" class="text-red-500 text-sm mt-1">{{ form.errors.direccion }}</p>
                    </div>

                    <!-- Contraseña (opcional al editar) -->
                    <div>
                        <p class="text-sm text-secondary mb-2">Deja en blanco si no deseas cambiar la contraseña</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-primary mb-2">Nueva Contraseña</label>
                                <input 
                                    v-model="form.password" 
                                    type="password" 
                                    class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                >
                                <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-primary mb-2">Confirmar Contraseña</label>
                                <input 
                                    v-model="form.password_confirmation" 
                                    type="password" 
                                    class="w-full px-4 py-2 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Roles -->
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Roles</label>
                        <div class="space-y-2">
                            <label 
                                v-for="role in roles" 
                                :key="role.id"
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input 
                                    v-model="form.roles" 
                                    type="checkbox" 
                                    :value="role.id"
                                    class="w-4 h-4"
                                >
                                <span class="text-primary">{{ role.nombre }}</span>
                                <span class="text-xs text-secondary">{{ role.descripcion }}</span>
                            </label>
                        </div>
                        <p v-if="form.errors.roles" class="text-red-500 text-sm mt-1">{{ form.errors.roles }}</p>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-4">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold disabled:opacity-50"
                        >
                            {{ form.processing ? 'Actualizando...' : 'Actualizar Usuario' }}
                        </button>
                        <Link 
                            href="/usuarios"
                            class="px-6 py-2 bg-accent/20 text-primary rounded-lg hover:bg-accent/30 transition font-semibold"
                        >
                            Cancelar
                        </Link>
                    </div>
                </form>
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
