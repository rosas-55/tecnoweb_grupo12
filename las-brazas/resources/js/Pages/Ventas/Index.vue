<template>
	<div>
		<h2 class="text-2xl font-semibold mb-4">Ventas</h2>

		<form @submit.prevent="submit" class="grid grid-cols-6 gap-3 mb-6">
			<input v-model="form.fecha" type="date" class="border p-2" />
			<input v-model="form.tipo" class="border p-2" placeholder="Tipo" />
			<input v-model.number="form.total" type="number" min="0" step="0.01" class="border p-2" placeholder="Total" />
			<select v-model="form.estado" class="border p-2">
				<option :value="1">Activa</option>
				<option :value="0">Anulada</option>
			</select>
			<select v-model="form.user_id" class="border p-2">
				<option disabled value="">Seleccione usuario</option>
				<option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
			</select>
			<button :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded">
				{{ editingId ? 'Actualizar' : 'Guardar' }}
			</button>
			<button v-if="editingId" @click.prevent="cancelEdit" class="px-3 py-2 border rounded">Cancelar edición</button>
		</form>

		<div class="bg-white rounded border">
			<table class="w-full">
				<thead class="bg-gray-100">
					<tr>
						<th class="text-left p-2">Fecha</th>
						<th class="text-left p-2">Tipo</th>
						<th class="text-right p-2">Total</th>
						<th class="text-left p-2">Usuario</th>
						<th class="text-left p-2">Estado</th>
						<th class="text-left p-2">Acciones</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="v in ventas.data" :key="v.id" class="border-t">
						<td class="p-2">{{ v.fecha }}</td>
						<td class="p-2">{{ v.tipo }}</td>
						<td class="p-2 text-right">{{ v.total }}</td>
						<td class="p-2">{{ v.user?.name }}</td>
						<td class="p-2">{{ v.estado ? 'Activa' : 'Anulada' }}</td>
						<td class="p-2">
							<button class="text-blue-600 mr-3" @click="startEdit(v)">Editar</button>
							<button class="text-red-600" @click="remove(v.id)">Eliminar</button>
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
const props = defineProps({
	ventas: Object,
	users: Array,
})
const ventas = props.ventas
const users = props.users
defineOptions({ layout: AdminLayout })

const today = new Date().toISOString().slice(0, 10)
const form = useForm({
	fecha: today,
	tipo: '',
	total: 0,
	estado: 1,
	user_id: '',
})

function submit() {
	if (editingId.value) {
		form.put(`/ventas/${editingId.value}`, { onSuccess: () => cancelEdit() })
	} else {
		form.post('/ventas', { onSuccess: () => form.reset({ fecha: today, estado: 1 }) })
	}
}
const editingId = ref(null)
function startEdit(v) {
	editingId.value = v.id
	form.fecha = v.fecha
	form.tipo = v.tipo
	form.total = v.total
	form.estado = v.estado ? 1 : 0
	form.user_id = v.user_id
}
function cancelEdit() {
	editingId.value = null
	form.reset({ fecha: today, estado: 1 })
}
function remove(id) {
	if (confirm('¿Eliminar venta?')) {
		router.delete(`/ventas/${id}`)
	}
}
</script>


