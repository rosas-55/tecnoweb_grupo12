<template>
	<div>
		<h1 class="text-2xl font-bold mb-4">Insumos</h1>
		<form @submit.prevent="submit" class="grid grid-cols-6 gap-3 mb-6 bg-white rounded border p-4">
			<div class="col-span-2">
				<label class="block text-sm text-gray-700 mb-1">Nombre</label>
				<input v-model="form.nombre" class="w-full border p-2" placeholder="Ej: carne de res" />
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Unidad de medida</label>
				<select v-model="form.unidad_medida" class="w-full border p-2">
					<option disabled value="">Seleccione unidad</option>
					<optgroup label="Peso">
						<option value="kg">Kilogramo (kg)</option>
						<option value="g">Gramo (g)</option>
						<option value="mg">Miligramo (mg)</option>
						<option value="lb">Libra (lb)</option>
						<option value="oz">Onza (oz)</option>
						<option value="arroba">Arroba (ar)</option>
					</optgroup>
					<optgroup label="Volumen">
						<option value="L">Litro (L)</option>
						<option value="mL">Mililitro (mL)</option>
						<option value="gal">Galón (gal)</option>
						<option value="fl oz">Onza líquida (fl oz)</option>
					</optgroup>
					<optgroup label="Unidades">
						<option value="unid">Unidad (unid)</option>
						<option value="pza">Pieza (pza)</option>
						<option value="caja">Caja</option>
						<option value="bolsa">Bolsa</option>
						<option value="paquete">Paquete</option>
						<option value="botella">Botella</option>
						<option value="lata">Lata</option>
						<option value="sobre">Sobre</option>
						<option value="frasco">Frasco</option>
						<option value="tubo">Tubo</option>
					</optgroup>
				</select>
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Stock mínimo</label>
				<input v-model.number="form.stock_minimo" type="number" min="0" step="0.01" class="w-full border p-2" />
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Costo unitario</label>
				<input v-model.number="form.costo_unitario" type="number" min="0" step="0.01" class="w-full border p-2" />
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Estado</label>
				<select v-model="form.estado" class="w-full border p-2">
					<option :value="1">Activo</option>
					<option :value="0">Inactivo</option>
				</select>
			</div>
			<div class="col-span-6">
				<label class="block text-sm text-gray-700 mb-1">Descripción</label>
				<input v-model="form.descripcion" class="w-full border p-2" placeholder="Opcional: notas del insumo" />
			</div>
			<button :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded">
				{{ editingId ? 'Actualizar' : 'Guardar' }}
			</button>
			<button v-if="editingId" @click.prevent="cancelEdit" class="px-3 py-2 border rounded">Cancelar edición</button>
		</form>

		<div class="bg-white shadow rounded">
			<table class="w-full">
				<thead class="bg-gray-100">
					<tr>
						<th class="text-left p-2">Nombre</th>
						<th class="text-left p-2">Unidad</th>
						<th class="text-right p-2">Stock actual</th>
						<th class="text-right p-2">Stock mínimo</th>
						<th class="text-right p-2">Costo</th>
						<th class="text-left p-2">Estado</th>
						<th class="text-left p-2">Acciones</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="i in insumos.data" :key="i.id" class="border-t">
						<td class="p-2">{{ i.nombre }}</td>
						<td class="p-2">{{ formatUnidad(i.unidad_medida) }}</td>
						<td class="p-2 text-right">{{ i.stock_actual }}</td>
						<td class="p-2 text-right">{{ i.stock_minimo }}</td>
						<td class="p-2 text-right">{{ i.costo_unitario }}</td>
						<td class="p-2">{{ i.estado ? 'Activo' : 'Inactivo' }}</td>
						<td class="p-2">
							<button class="text-blue-600 mr-3" @click="startEdit(i)">Editar</button>
							<button class="text-red-600" @click="remove(i.id)">Eliminar</button>
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
const props = defineProps({ insumos: Object })
const insumos = props.insumos

const form = useForm({
	nombre: '',
	descripcion: '',
	unidad_medida: '',
	stock_minimo: 0,
	stock_actual: 0,
	costo_unitario: 0,
	estado: 1
})

function submit() {
	if (editingId.value) {
		// Al editar, no permitir cambiar stock_actual (se mantiene el valor actual)
		// No lo incluimos en el formulario, así que no se enviará
		form.put(`/insumos/${editingId.value}`, { onSuccess: () => cancelEdit() })
	} else {
		// Al crear, asegurar que stock_actual siempre sea 0
		form.stock_actual = 0
		form.post('/insumos', { onSuccess: () => form.reset() })
	}
}

const editingId = ref(null)
function startEdit(i) {
	editingId.value = i.id
	form.nombre = i.nombre
	form.descripcion = i.descripcion ?? ''
	form.unidad_medida = i.unidad_medida
	form.stock_minimo = i.stock_minimo
	// stock_actual no se edita, solo se actualiza mediante movimientos de inventario
	form.costo_unitario = i.costo_unitario
	form.estado = i.estado ? 1 : 0
}
function cancelEdit() {
	editingId.value = null
	form.reset()
}
function remove(id) {
	if (confirm('¿Eliminar insumo?')) {
		router.delete(`/insumos/${id}`)
	}
}

function formatUnidad(unidad) {
	if (unidad === 'arroba') return 'ar'
	return unidad
}
</script>

<script>
export default { layout: (h, page) => h(AdminLayout, () => page) }
</script>


