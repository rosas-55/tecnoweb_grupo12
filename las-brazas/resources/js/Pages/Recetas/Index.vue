<template>
	<div>
		<h1 class="text-2xl font-bold mb-4">Recetas</h1>
		<form @submit.prevent="submit" class="mb-6 bg-white rounded border p-4 space-y-4">
			<div class="grid grid-cols-6 gap-3">
				<div class="col-span-2">
					<label class="block text-sm text-gray-700 mb-1">Nombre</label>
					<input v-model="form.nombre" class="w-full border p-2" placeholder="Ej: Pollo a la broaster" />
				</div>
				<div>
					<label class="block text-sm text-gray-700 mb-1">Tiempo preparación (min)</label>
					<input v-model.number="form.tiempo_preparacion" type="number" min="0" class="w-full border p-2" />
				</div>
				<div>
					<label class="block text-sm text-gray-700 mb-1">Estado</label>
					<select v-model="form.estado" class="w-full border p-2">
						<option :value="1">Activa</option>
						<option :value="0">Inactiva</option>
					</select>
				</div>
				<div class="col-span-6">
					<label class="block text-sm text-gray-700 mb-1">Indicaciones / Descripción</label>
					<textarea
						v-model="form.descripcion"
						class="w-full border p-2 min-h-[150px]"
						placeholder="Escribe las indicaciones de la receta aquí. Puedes usar saltos de línea, enumeraciones, etc."
					></textarea>
					<p class="text-xs text-gray-500 mt-1">Puedes escribir texto largo con formato, enumeraciones, títulos, etc.</p>
				</div>
			</div>

			<div>
				<h2 class="font-semibold mb-2">Insumos de la receta</h2>
				<div class="space-y-2">
					<div
						v-for="(item, index) in form.items"
						:key="index"
						class="grid grid-cols-6 gap-3 items-center"
					>
						<div class="col-span-3">
							<label class="block text-xs text-gray-600 mb-1">Insumo</label>
							<select v-model="item.insumo_id" class="w-full border p-2">
								<option disabled value="">Seleccione insumo</option>
								<option
									v-for="i in insumos"
									:key="i.id"
									:value="i.id"
								>
									{{ i.nombre }} ({{ i.unidad_medida }})
								</option>
							</select>
						</div>
						<div>
							<label class="block text-xs text-gray-600 mb-1">Cantidad</label>
							<input
								v-model.number="item.cantidad"
								type="number"
								min="0"
								step="0.01"
								class="w-full border p-2"
							/>
						</div>
						<div>
							<button
								type="button"
								class="text-red-600 text-sm mt-5"
								@click="removeItem(index)"
							>
								Quitar
							</button>
						</div>
					</div>
					<button
						type="button"
						class="mt-2 text-sm text-blue-600"
						@click="addItem"
					>
						+ Agregar insumo
					</button>
				</div>
			</div>

			<div class="flex gap-3">
				<button type="submit" :disabled="form.processing || isSubmitting" class="bg-blue-600 text-white px-4 py-2 rounded">
					{{ editingId ? 'Actualizar' : 'Guardar' }}
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
						<th class="text-left p-2">Nombre</th>
						<th class="text-left p-2">Tiempo</th>
						<th class="text-left p-2">Estado</th>
						<th class="text-left p-2">Insumos</th>
						<th class="text-left p-2">Indicaciones</th>
						<th class="text-left p-2">Acciones</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="r in recetas" :key="r.id" class="border-t align-top">
						<td class="p-2">{{ r.nombre }}</td>
						<td class="p-2">{{ r.tiempo_preparacion }} min</td>
						<td class="p-2">{{ r.estado ? 'Activa' : 'Inactiva' }}</td>
						<td class="p-2 text-sm text-gray-700">
							<div v-if="r.insumos && r.insumos.length">
								<span class="mr-2">{{ r.insumos.length }}</span>
								<button
									type="button"
									class="text-blue-600 text-sm underline"
									@click="openModalInsumos(r)"
								>
									Ver detalle
								</button>
							</div>
							<div v-else class="text-gray-400">0</div>
						</td>
						<td class="p-2 text-sm text-gray-700">
							<div v-if="r.descripcion && r.descripcion.trim()">
								<button
									type="button"
									class="text-blue-600 text-sm underline"
									@click="openModalIndicaciones(r)"
								>
									Ver indicaciones
								</button>
							</div>
							<div v-else class="text-gray-400">Sin indicaciones</div>
						</td>
						<td class="p-2">
							<button class="text-blue-600 mr-3" @click="startEdit(r)">Editar</button>
							<button class="text-red-600" @click="remove(r.id)">Eliminar</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<!-- Modal detalle de insumos -->
		<div
			v-if="showModalInsumos && selectedRecetaInsumos"
			class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
		>
			<div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6">
				<h3 class="text-xl font-semibold mb-2">
					Insumos de: {{ selectedRecetaInsumos.nombre }}
				</h3>
				<p class="text-sm text-gray-600 mb-4">
					Cantidad de producto: <span class="font-semibold">1 lote</span> (usa las cantidades indicadas abajo).
				</p>
				<div v-if="selectedRecetaInsumos.insumos && selectedRecetaInsumos.insumos.length">
					<ul class="space-y-2">
						<li v-for="ri in selectedRecetaInsumos.insumos" :key="ri.id" class="flex justify-between items-center p-2 bg-gray-50 rounded border">
							<span class="font-medium">{{ ri.nombre }}</span>
							<span class="text-sm text-gray-600">
								<span class="font-semibold text-blue-700">{{ formatNumber(ri.pivot?.cantidad || 0) }}</span>
								<span class="text-gray-500"> {{ ri.unidad_medida }}</span>
							</span>
						</li>
					</ul>
				</div>
				<div v-else class="text-gray-500 text-sm">
					Esta receta aún no tiene insumos asociados.
				</div>
				<div class="mt-6 text-right">
					<button
						type="button"
						class="px-4 py-2 border rounded"
						@click="closeModalInsumos"
					>
						Cerrar
					</button>
				</div>
			</div>
		</div>

		<!-- Modal indicaciones de la receta -->
		<div
			v-if="showModalIndicaciones && selectedRecetaIndicaciones"
			class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
		>
			<div class="bg-white rounded-lg shadow-lg max-w-3xl w-full p-6 max-h-[80vh] overflow-y-auto">
				<h3 class="text-xl font-semibold mb-2">
					Indicaciones de: {{ selectedRecetaIndicaciones.nombre }}
				</h3>
				<div v-if="selectedRecetaIndicaciones.descripcion && selectedRecetaIndicaciones.descripcion.trim()" class="mt-4">
					<div class="bg-gray-50 rounded border p-4 whitespace-pre-wrap text-gray-800 leading-relaxed">
						{{ selectedRecetaIndicaciones.descripcion }}
					</div>
				</div>
				<div v-else class="text-gray-500 text-sm mt-4">
					Esta receta no tiene indicaciones registradas.
				</div>
				<div class="mt-6 text-right">
					<button
						type="button"
						class="px-4 py-2 border rounded"
						@click="closeModalIndicaciones"
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
import { ref } from 'vue'
import AdminLayout from '../../Layouts/AdminLayout.vue'
const props = defineProps({ recetas: Array, insumos: Array })
const recetas = props.recetas
const insumos = props.insumos

const showModalInsumos = ref(false)
const selectedRecetaInsumos = ref(null)
const showModalIndicaciones = ref(false)
const selectedRecetaIndicaciones = ref(null)
const editingId = ref(null)
const isSubmitting = ref(false)

const form = useForm({
	nombre: '',
	descripcion: '',
	tiempo_preparacion: 0,
	estado: 1,
	items: [],
})

function submit(e) {
	if (e) {
		e.preventDefault()
		e.stopPropagation()
	}

	// Prevenir envíos duplicados
	if (isSubmitting.value || form.processing) {
		return
	}

	// Capturar el ID ANTES de cualquier operación
	const currentEditingId = editingId.value

	// Si NO hay ID, crear nueva receta
	if (!currentEditingId) {
		form.post('/recetas', {
			onSuccess: () => resetForm(),
			onError: (errors) => {
				console.error('Error al crear:', errors)
			}
		})
		return
	}

	// Validar que el ID sea válido
	const recetaId = Number(currentEditingId)
	if (!recetaId || isNaN(recetaId) || recetaId <= 0) {
		alert('Error: ID de receta inválido. Por favor, cancela la edición e intenta de nuevo.')
		cancelEdit()
		return
	}

	// Construir la URL con el ID
	const url = `/recetas/${recetaId}`

	// Preparar los datos del formulario
	const formData = {
		nombre: form.nombre,
		descripcion: form.descripcion,
		tiempo_preparacion: form.tiempo_preparacion,
		estado: form.estado,
		items: form.items
	}

	// Marcar como procesando
	isSubmitting.value = true

	// Usar router.visit con método PUT directamente
	router.visit(url, {
		method: 'put',
		data: formData,
		preserveState: false,
		preserveScroll: true,
		onSuccess: () => {
			isSubmitting.value = false
			cancelEdit()
		},
		onError: (errors) => {
			isSubmitting.value = false
			console.error('Error al actualizar:', errors)
		},
		onFinish: () => {
			isSubmitting.value = false
		}
	})
}
function startEdit(r) {
	if (!r || !r.id) {
		alert('Error: Receta inválida')
		return
	}

	editingId.value = Number(r.id)
	form.nombre = r.nombre || ''
	form.descripcion = r.descripcion ?? ''
	form.tiempo_preparacion = r.tiempo_preparacion || 0
	form.estado = r.estado ? 1 : 0
	form.items = (r.insumos || []).map((ri) => ({
		insumo_id: ri.id,
		cantidad: ri.pivot?.cantidad ?? 0,
	}))
}
function cancelEdit() {
	editingId.value = null
	resetForm()
}
function remove(id) {
	if (confirm('¿Eliminar receta?')) {
		router.delete(`/recetas/${id}`)
	}
}

// Formatear números (mostrar decimales solo si es necesario)
function formatNumber(num) {
	if (num % 1 === 0) {
		return num.toString()
	}
	return num.toFixed(2).replace(/\.?0+$/, '')
}

function openModalInsumos(r) {
	selectedRecetaInsumos.value = r
	showModalInsumos.value = true
}
function closeModalInsumos() {
	showModalInsumos.value = false
	selectedRecetaInsumos.value = null
}

function openModalIndicaciones(r) {
	selectedRecetaIndicaciones.value = r
	showModalIndicaciones.value = true
}
function closeModalIndicaciones() {
	showModalIndicaciones.value = false
	selectedRecetaIndicaciones.value = null
}

function addItem() {
	form.items.push({ insumo_id: '', cantidad: 0 })
}
function removeItem(index) {
	form.items.splice(index, 1)
}
function resetForm() {
	form.reset()
	form.items = []
}
</script>

<script>
export default { layout: (h, page) => h(AdminLayout, () => page) }
</script>


