<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    usuarios: Object,
    search: String
});

const page = usePage();
const searchQuery = ref(props.search || '');

const handleSearch = (query) => {
    router.get('/usuarios', { search: query }, {
        preserveState: true,
        preserveScroll: true
    });
};

const deleteUsuario = (id) => {
    if (confirm('¿Estás seguro de eliminar este usuario?')) {
        console.log('Intentando eliminar usuario ID:', id);

        router.delete(`/usuarios/${id}`, {
            preserveScroll: false,
            onBefore: () => {
                console.log('Enviando petición DELETE...');
            },
            onStart: () => {
                console.log('Petición iniciada');
            },
            onSuccess: (page) => {
                console.log('✅ Usuario eliminado exitosamente', page);
                // Mostrar mensaje de éxito si está disponible
                if (page.props?.flash?.success) {
                    alert(page.props.flash.success);
                } else {
                    alert('Usuario eliminado exitosamente');
                }
                // Recargar la lista
                router.reload({ only: ['usuarios'] });
            },
            onError: (errors) => {
                console.error('❌ Error al eliminar usuario:', errors);
                console.error('Tipo de error:', typeof errors);
                console.error('Contenido completo:', JSON.stringify(errors, null, 2));

                let errorMessage = 'Error al eliminar el usuario.';

                if (errors?.message) {
                    errorMessage = errors.message;
                } else if (typeof errors === 'string') {
                    errorMessage = errors;
                } else if (errors?.error) {
                    errorMessage = errors.error;
                } else if (errors?.exception) {
                    errorMessage = 'Error del servidor: ' + errors.exception;
                }

                alert(errorMessage);
            },
            onFinish: () => {
                console.log('Petición finalizada');
            }
        });
    }
};

// Mostrar mensajes flash
onMounted(() => {
    if (page.props.flash?.success) {
        alert(page.props.flash.success);
    }
    if (page.props.flash?.error) {
        alert(page.props.flash.error);
    }
});

// Observar cambios en los mensajes flash
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        alert(flash.success);
    }
    if (flash?.error) {
        alert(flash.error);
    }
}, { deep: true });
</script>

<template>
    <MainLayout
        title="Gestión de Usuarios"
        search-placeholder="Buscar usuarios por nombre, email o cédula..."
        :on-search="handleSearch"
    >
        <div class="space-y-4">
            <!-- Botón crear -->
            <div class="flex justify-end">
                <Link
                    href="/usuarios/create"
                    class="px-4 py-2 bg-accent text-white rounded-lg hover:opacity-90 transition font-semibold"
                >
                    ➕ Crear Usuario
                </Link>
            </div>

            <!-- Tabla -->
            <div class="bg-card rounded-lg shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-accent/20">
                        <tr>
                            <th class="px-4 py-3 text-left text-primary font-semibold">Nombre</th>
                            <th class="px-4 py-3 text-left text-primary font-semibold">Cédula</th>
                            <th class="px-4 py-3 text-left text-primary font-semibold">Email</th>
                            <th class="px-4 py-3 text-left text-primary font-semibold">Celular</th>
                            <th class="px-4 py-3 text-left text-primary font-semibold">Roles</th>
                            <th class="px-4 py-3 text-left text-primary font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="usuario in usuarios.data"
                            :key="usuario.id"
                            class="border-t border-accent/10 hover:bg-accent/5"
                        >
                            <td class="px-4 py-3 text-primary">{{ usuario.name }}</td>
                            <td class="px-4 py-3 text-secondary">{{ usuario.cedula }}</td>
                            <td class="px-4 py-3 text-secondary">{{ usuario.email }}</td>
                            <td class="px-4 py-3 text-secondary">{{ usuario.celular || 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span
                                    v-for="role in usuario.roles"
                                    :key="role.id"
                                    class="inline-block px-2 py-1 text-xs rounded-full bg-accent text-white mr-1"
                                >
                                    {{ role.nombre }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Link
                                        :href="`/usuarios/${usuario.id}/edit`"
                                        class="text-blue-500 hover:underline"
                                    >
                                        ✏️ Editar
                                    </Link>
                                    <button
                                        @click="deleteUsuario(usuario.id)"
                                        class="text-red-500 hover:underline"
                                    >
                                        🗑️ Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="usuarios.links" class="flex justify-between items-center">
                <p class="text-secondary">
                    Mostrando {{ usuarios.from }} a {{ usuarios.to }} de {{ usuarios.total }} usuarios
                </p>
                <div class="flex gap-2">
                    <template v-for="link in usuarios.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 rounded',
                                link.active ? 'bg-accent text-white' : 'bg-accent/20 text-primary hover:bg-accent/30'
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            :class="[
                                'px-3 py-2 rounded opacity-50 cursor-not-allowed',
                                link.active ? 'bg-accent text-white' : 'bg-accent/20 text-primary'
                            ]"
                            v-html="link.label"
                        />
                    </template>
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
