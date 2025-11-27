<template>
	<div>
		<h1 class="text-2xl font-bold mb-4">Producción</h1>
		<form @submit.prevent="submit" class="mb-6 bg-white rounded border p-4 space-y-4">
			<div class="grid grid-cols-6 gap-3">
				<div class="col-span-2">
					<label class="block text-sm text-gray-700 mb-1">Receta</label>
					<select v-model="form.receta_id" class="w-full border p-2">
						<option disabled value="">Seleccione receta</option>
						<option v-for="r in recetas" :key="r.id" :value="r.id">{{ r.nombre }}</option>
					</select>
				</div>
				<div>
					<label class="block text-sm text-gray-700 mb-1">Cantidad de platos</label>
					<input v-model.number="form.cantidad_producida" type="number" min="1" class="w-full border p-2" placeholder="Ej: 10" />
				</div>
				<div>
					<label class="block text-sm text-gray-700 mb-1">Fecha</label>
					<input v-model="form.fecha" type="date" class="w-full border p-2" />
				</div>
			</div>

			<!-- Sección de insumos calculados - Lista simple -->
			<div v-if="recetaSeleccionada && recetaSeleccionada.insumos && recetaSeleccionada.insumos.length > 0" class="mt-4 p-4 bg-gray-50 rounded border">
				<h3 class="font-semibold mb-3 text-gray-700">
					Insumos necesarios para {{ form.cantidad_producida }} plato{{ form.cantidad_producida !== 1 ? 's' : '' }} de "{{ recetaSeleccionada.nombre }}":
				</h3>
				<ul class="space-y-2">
					<li v-for="insumo in insumosCalculados" :key="insumo.id" class="flex justify-between items-center bg-white p-2 rounded border">
						<span class="font-medium text-gray-800">{{ insumo.nombre }}</span>
						<span class="text-sm text-gray-600">
							<span class="font-semibold text-blue-700">{{ formatNumber(insumo.cantidad_total) }}</span>
							<span class="text-gray-500"> {{ insumo.unidad_medida }}</span>
							<span class="text-gray-400 text-xs ml-2">
								({{ formatNumber(insumo.cantidad_por_plato) }} {{ insumo.unidad_medida }} × {{ form.cantidad_producida }})
							</span>
						</span>
					</li>
				</ul>
			</div>
			<div v-else-if="form.receta_id" class="mt-4 p-4 bg-yellow-50 rounded border border-yellow-200">
				<p class="text-sm text-yellow-800">Esta receta no tiene insumos asociados.</p>
			</div>

			<div class="flex gap-3 mt-4">
				<button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded">
					{{ editingId ? 'Actualizar' : 'Registrar' }}
				</button>
				<button type="button" v-if="editingId" @click.prevent="cancelEdit" class="px-3 py-2 border rounded">
					Cancelar edición
				</button>
			</div>
		</form>

		<div class="bg-white shadow rounded">
			<table class="w-full">
				<thead class="bg-gray-100">
					<tr>
						<th class="text-left p-2">Fecha</th>
						<th class="text-left p-2">Receta</th>
						<th class="text-right p-2">Cantidad</th>
						<th class="text-left p-2">Insumos</th>
						<th class="text-left p-2">Acciones</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="p in producciones.data" :key="p.id" class="border-t">
						<td class="p-2">{{ p.fecha }}</td>
						<td class="p-2">{{ p.receta?.nombre }}</td>
						<td class="p-2 text-right">{{ p.cantidad_producida }}</td>
						<td class="p-2 text-sm text-gray-700">
							<div v-if="p.receta && p.receta.insumos && p.receta.insumos.length">
								<span class="mr-2">{{ p.receta.insumos.length }}</span>
								<button
									type="button"
									class="text-blue-600 text-sm underline"
									@click="openModal(p)"
								>
									Ver detalle
								</button>
							</div>
							<div v-else class="text-gray-400">0</div>
						</td>
						<td class="p-2">
							<button class="text-blue-600 mr-3" @click="startEdit(p)">Editar</button>
							<button class="text-red-600" @click="remove(p.id)">Eliminar</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Modal detalle de insumos de producción -->
		<div
			v-if="showModal && selectedProduccion"
			class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
		>
			<div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6">
				<h3 class="text-xl font-semibold mb-2">
					Insumos de: {{ selectedProduccion.receta?.nombre }}
				</h3>
				<p class="text-sm text-gray-600 mb-4">
					Cantidad producida: <span class="font-semibold">{{ selectedProduccion.cantidad_producida }}</span> plato{{ selectedProduccion.cantidad_producida !== 1 ? 's' : '' }}
				</p>
				<div v-if="insumosProduccion && insumosProduccion.length">
					<ul class="space-y-2">
						<li v-for="item in insumosProduccion" :key="item.id" class="flex justify-between items-center p-2 bg-gray-50 rounded border">
							<span class="font-medium">{{ item.nombre }}</span>
							<span class="text-sm text-gray-600">
								<span class="font-semibold text-blue-700">{{ formatNumber(item.cantidad_total) }}</span>
								<span class="text-gray-500"> {{ item.unidad_medida }}</span>
								<span class="text-gray-400 text-xs ml-2">
									({{ formatNumber(item.cantidad_por_plato) }} {{ item.unidad_medida }} × {{ selectedProduccion.cantidad_producida }})
								</span>
							</span>
						</li>
					</ul>
				</div>
				<div v-else class="text-gray-500 text-sm">
					Esta producción no tiene insumos asociados.
				</div>
				<div class="mt-6 text-right">
					<button
						type="button"
						class="px-4 py-2 border rounded"
						@click="closeModal"
					>
						Cerrar
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AdminLayout from '../../Layouts/AdminLayout.vue'
const props = defineProps({ producciones: Object, recetas: Array })
const producciones = props.producciones
const recetas = props.recetas

const today = new Date().toISOString().slice(0, 10)
const form = useForm({
	receta_id: '',
	cantidad_producida: 1,
	fecha: today
})

const showModal = ref(false)
const selectedProduccion = ref(null)

// Encontrar la receta seleccionada
const recetaSeleccionada = computed(() => {
	if (!form.receta_id) return null
	return recetas.find(r => Number(r.id) === Number(form.receta_id))
})

// Calcular insumos multiplicados por la cantidad
const insumosCalculados = computed(() => {
	if (!recetaSeleccionada.value || !recetaSeleccionada.value.insumos) {
		return []
	}

	const cantidad = form.cantidad_producida || 1

	return recetaSeleccionada.value.insumos.map(insumo => {
		const cantidadPorPlato = insumo.pivot?.cantidad || 0
		const cantidadTotal = cantidadPorPlato * cantidad

		return {
			id: insumo.id,
			nombre: insumo.nombre,
			unidad_medida: insumo.unidad_medida,
			cantidad_por_plato: cantidadPorPlato,
			cantidad_total: cantidadTotal
		}
	})
})

// Calcular insumos de la producción seleccionada para el modal
const insumosProduccion = computed(() => {
	if (!selectedProduccion.value || !selectedProduccion.value.receta || !selectedProduccion.value.receta.insumos) {
		return []
	}

	const cantidad = selectedProduccion.value.cantidad_producida || 1

	return selectedProduccion.value.receta.insumos.map(insumo => {
		const cantidadPorPlato = insumo.pivot?.cantidad || 0
		const cantidadTotal = cantidadPorPlato * cantidad

		return {
			id: insumo.id,
			nombre: insumo.nombre,
			unidad_medida: insumo.unidad_medida,
			cantidad_por_plato: cantidadPorPlato,
			cantidad_total: cantidadTotal
		}
	})
})

// Formatear números (mostrar decimales solo si es necesario)
function formatNumber(num) {
	if (num % 1 === 0) {
		return num.toString()
	}
	return num.toFixed(2).replace(/\.?0+$/, '')
}

function openModal(p) {
	selectedProduccion.value = p
	showModal.value = true
}

function closeModal() {
	showModal.value = false
	selectedProduccion.value = null
}

function submit() {
	if (editingId.value) {
		form.put(`/produccion/${editingId.value}`, { onSuccess: () => cancelEdit() })
	} else {
		form.post('/produccion', { onSuccess: () => {
			form.reset()
			form.fecha = today
			form.cantidad_producida = 1
		}})
	}
}
const editingId = ref(null)
function startEdit(p) {
	editingId.value = p.id
	form.receta_id = p.receta_id
	form.cantidad_producida = p.cantidad_producida
	form.fecha = p.fecha
}
function cancelEdit() {
	editingId.value = null
	form.reset()
	form.fecha = today
	form.cantidad_producida = 1
}
function remove(id) {
	if (confirm('¿Eliminar registro de producción?')) {
		router.delete(`/produccion/${id}`)
	}
}
</script>

<script>
export default { layout: (h, page) => h(AdminLayout, () => page) }
</script>


