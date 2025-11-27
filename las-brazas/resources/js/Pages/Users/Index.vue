<template>
	<div>
		<h2 class="text-2xl font-semibold mb-4">Usuarios</h2>

		<!-- Formulario crear/editar -->
		<form @submit.prevent="submit" class="grid grid-cols-6 gap-3 mb-6 bg-white rounded border p-4">
			<div class="col-span-2">
				<label class="block text-sm text-gray-700 mb-1">Nombre</label>
				<input v-model="form.name" class="w-full border p-2" placeholder="Ej: Juan Pérez" />
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Cédula</label>
				<input v-model="form.cedula" class="w-full border p-2" placeholder="Ej: 12345678" />
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Celular</label>
				<input v-model="form.celular" class="w-full border p-2" placeholder="Ej: 70000000" />
			</div>
			<div class="col-span-2">
				<label class="block text-sm text-gray-700 mb-1">Dirección</label>
				<input v-model="form.direccion" class="w-full border p-2" placeholder="Ej: Av. Principal #123" />
			</div>
			<div class="col-span-2">
				<label class="block text-sm text-gray-700 mb-1">Email</label>
				<input v-model="form.email" type="email" class="w-full border p-2" placeholder="Ej: usuario@email.com" />
			</div>
			<div class="col-span-2">
				<label class="block text-sm text-gray-700 mb-1">Contraseña</label>
				<input v-model="form.password" type="password" class="w-full border p-2" placeholder="Mínimo 6 caracteres" />
			</div>
			<div class="col-span-2">
				<label class="block text-sm text-gray-700 mb-1">Rol</label>
				<select v-model="form.role_id" class="w-full border p-2">
					<option :value="null">Sin rol</option>
					<option v-for="r in roles" :key="r.id" :value="r.id">{{ r.nombre }}</option>
				</select>
			</div>

			<div class="col-span-6 flex gap-3">
				<button :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded">
					{{ editingId ? 'Actualizar' : 'Guardar' }}
				</button>
				<button v-if="editingId" @click.prevent="cancelEdit" class="px-3 py-2 border rounded">
					Cancelar edición
				</button>
			</div>
		</form>

		<!-- Tabla -->
		<div class="bg-white rounded border">
			<table class="w-full">
				<thead class="bg-gray-100">
					<tr>
						<th class="text-left p-2">ID</th>
						<th class="text-left p-2">Nombre</th>
						<th class="text-left p-2">Email</th>
						<th class="text-left p-2">Rol</th>
						<th class="text-left p-2">Acciones</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="u in users.data" :key="u.id" class="border-t">
						<td class="p-2">{{ u.id }}</td>
						<td class="p-2">{{ u.name }}</td>
						<td class="p-2">{{ u.email }}</td>
						<td class="p-2">{{ u.roles?.[0]?.nombre ?? '—' }}</td>
						<td class="p-2">
							<button class="text-blue-600 mr-3" @click="startEdit(u)">Editar</button>
							<button class="text-red-600" @click="remove(u.id)">Eliminar</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'
import AdminLayout from '../../Layouts/AdminLayout.vue'
defineOptions({ layout: AdminLayout })

const props = defineProps({
	users: Object,
	roles: Array,
})

const users = props.users
const roles = props.roles

const editingId = ref(null)
const form = useForm({
	name: '',
	cedula: '',
	celular: '',
	direccion: '',
	email: '',
	password: '',
	role_id: null,
})

function startEdit(u) {
	editingId.value = u.id
	form.name = u.name
	form.cedula = u.cedula ?? ''
	form.celular = u.celular ?? ''
	form.direccion = u.direccion ?? ''
	form.email = u.email
	form.password = ''
	form.role_id = u.roles?.[0]?.id ?? null
}

function cancelEdit() {
	editingId.value = null
	form.reset()
}

function submit() {
	if (editingId.value) {
		form.put(`/users/${editingId.value}`, {
			onSuccess: () => cancelEdit(),
		})
	} else {
		form.post('/users', {
			onSuccess: () => form.reset(),
		})
	}
}

function remove(id) {
	if (confirm('¿Eliminar usuario?')) {
		router.delete(`/users/${id}`)
	}
}
</script>


