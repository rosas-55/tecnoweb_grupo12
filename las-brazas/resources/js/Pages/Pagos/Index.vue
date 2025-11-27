<template>
	<div>
		<h2 class="text-2xl font-semibold mb-4">Pagos</h2>

		<form @submit.prevent="submit" class="grid grid-cols-6 gap-3 mb-6">
			<input v-model="form.fechapago" type="date" class="border p-2" />
			<select v-model="form.venta_id" class="col-span-2 border p-2">
				<option disabled value="">Seleccione venta</option>
				<option v-for="v in ventas" :key="v.id" :value="v.id">#{{ v.id }} - {{ v.total }}</option>
			</select>
			<input v-model="form.metodopago" class="border p-2" placeholder="Método" />
			<input v-model.number="form.monto" type="number" min="0" step="0.01" class="border p-2" placeholder="Monto" />
			<select v-model="form.estado" class="border p-2">
				<option :value="1">Confirmado</option>
				<option :value="0">Pendiente</option>
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
						<th class="text-left p-2">Venta</th>
						<th class="text-left p-2">Método</th>
						<th class="text-right p-2">Monto</th>
						<th class="text-left p-2">Estado</th>
						<th class="text-left p-2">Acciones</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="p in pagos.data" :key="p.id" class="border-t">
						<td class="p-2">{{ p.fechapago }}</td>
						<td class="p-2">#{{ p.venta_id }}</td>
						<td class="p-2">{{ p.metodopago }}</td>
						<td class="p-2 text-right">{{ p.monto }}</td>
						<td class="p-2">{{ p.estado ? 'Confirmado' : 'Pendiente' }}</td>
						<td class="p-2">
							<button class="text-blue-600 mr-3" @click="startEdit(p)">Editar</button>
							<button class="text-red-600" @click="remove(p.id)">Eliminar</button>
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
	pagos: Object,
	ventas: Array,
})
const pagos = props.pagos
const ventas = props.ventas
defineOptions({ layout: AdminLayout })

const today = new Date().toISOString().slice(0, 10)
const form = useForm({
	fechapago: today,
	venta_id: '',
	metodopago: '',
	monto: 0,
	estado: 1,
})

function submit() {
	if (editingId.value) {
		form.put(`/pagos/${editingId.value}`, { onSuccess: () => cancelEdit() })
	} else {
		form.post('/pagos', { onSuccess: () => form.reset({ fechapago: today, estado: 1 }) })
	}
}
const editingId = ref(null)
function startEdit(p) {
	editingId.value = p.id
	form.fechapago = p.fechapago
	form.venta_id = p.venta_id
	form.metodopago = p.metodopago
	form.monto = p.monto
	form.estado = p.estado ? 1 : 0
}
function cancelEdit() {
	editingId.value = null
	form.reset({ fechapago: today, estado: 1 })
}
function remove(id) {
	if (confirm('¿Eliminar pago?')) {
		router.delete(`/pagos/${id}`)
	}
}
</script>


