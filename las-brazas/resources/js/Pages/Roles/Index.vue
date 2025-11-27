<template>
	<div>
		<h2 class="text-2xl font-semibold mb-4">Roles</h2>

		<form @submit.prevent="submit" class="grid grid-cols-6 gap-3 mb-6 bg-white rounded border p-4">
			<div class="col-span-2">
				<label class="block text-sm text-gray-700 mb-1">Nombre</label>
				<input v-model="form.nombre" class="w-full border p-2" placeholder="Ej: Administrador" />
			</div>
			<div class="col-span-4">
				<label class="block text-sm text-gray-700 mb-1">Descripción</label>
				<input v-model="form.descripcion" class="w-full border p-2" placeholder="Ej: Acceso total al sistema" />
			</div>
			<div class="col-span-6 flex gap-3">
				<button :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded">
					{{ editingId ? 'Actualizar' : 'Guardar' }}
				</button>
				<button v-if="editingId" @click.prevent="cancelEdit" class="px-3 py-2 border rounded">Cancelar edición</button>
			</div>
		</form>

		<div class="bg-white rounded border">
			<table class="w-full">
				<thead class="bg-gray-100">
					<tr>
						<th class="text-left p-2">ID</th>
						<th class="text-left p-2">Nombre</th>
						<th class="text-left p-2">Descripción</th>
						<th class="text-left p-2">Acciones</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="r in roles.data" :key="r.id" class="border-t">
						<td class="p-2">{{ r.id }}</td>
						<td class="p-2">{{ r.nombre }}</td>
						<td class="p-2">{{ r.descripcion }}</td>
						<td class="p-2">
							<button class="text-blue-600 mr-3" @click="startEdit(r)">Editar</button>
							<button class="text-red-600" @click="remove(r.id)">Eliminar</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '../../Layouts/AdminLayout.vue'
defineOptions({ layout: AdminLayout })

const props = defineProps({
	roles: Object,
})

const roles = props.roles
const editingId = ref(null)
const form = useForm({
	nombre: '',
	descripcion: '',
})

function startEdit(r) {
	editingId.value = r.id
	form.nombre = r.nombre
	form.descripcion = r.descripcion ?? ''
}

function cancelEdit() {
	editingId.value = null
	form.reset()
}

function submit() {
	if (editingId.value) {
		form.put(`/roles/${editingId.value}`, {
			onSuccess: () => cancelEdit(),
		})
	} else {
		form.post('/roles', {
			onSuccess: () => form.reset(),
		})
	}
}

function remove(id) {
	if (confirm('¿Eliminar rol?')) {
		router.delete(`/roles/${id}`)
	}
}
</script>


