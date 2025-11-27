<template>
	<div>
		<h2 class="text-2xl font-semibold mb-4">Inventario</h2>

		<form @submit.prevent="submit" class="grid grid-cols-6 gap-3 mb-6 bg-white rounded border p-4">
			<div class="col-span-2">
				<label class="block text-sm text-gray-700 mb-1">Insumo</label>
				<select v-model="form.insumo_id" class="w-full border p-2">
					<option disabled value="">Seleccione insumo</option>
					<option v-for="i in insumos" :key="i.id" :value="i.id">{{ i.nombre }}</option>
				</select>
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Tipo de movimiento</label>
				<select v-model="form.tipo_movimiento" class="w-full border p-2">
					<option value="Entrada">Entrada</option>
					<option value="Salida">Salida</option>
				</select>
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Cantidad</label>
				<input
					v-model.number="form.cantidad"
					type="number"
					min="0"
					step="0.01"
					class="w-full border p-2"
				/>
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Unidad</label>
				<select v-model="form.unidad_movimiento" class="w-full border p-2">
					<option disabled value="">Seleccione unidad</option>
					<template v-if="insumoSeleccionado">
						<!-- Unidades de Peso - Siempre visible -->
						<optgroup label="Peso">
							<option value="kg">
								Kilogramo (kg){{ insumoSeleccionado.unidad_medida === 'kg' ? ' - unidad base' : '' }}
							</option>
							<option value="g">
								Gramo (g){{ insumoSeleccionado.unidad_medida === 'g' ? ' - unidad base' : '' }}
							</option>
							<option value="mg">
								Miligramo (mg){{ insumoSeleccionado.unidad_medida === 'mg' ? ' - unidad base' : '' }}
							</option>
							<option value="lb">
								Libra (lb){{ insumoSeleccionado.unidad_medida === 'lb' ? ' - unidad base' : '' }}
							</option>
							<option value="oz">
								Onza (oz){{ insumoSeleccionado.unidad_medida === 'oz' ? ' - unidad base' : '' }}
							</option>
							<option value="arroba">
								Arroba (ar){{ insumoSeleccionado.unidad_medida === 'arroba' ? ' - unidad base' : '' }}
							</option>
						</optgroup>
						<!-- Unidades de Volumen - Siempre visible -->
						<optgroup label="Volumen">
							<option value="L">
								Litro (L){{ insumoSeleccionado.unidad_medida === 'L' ? ' - unidad base' : '' }}
							</option>
							<option value="mL">
								Mililitro (mL){{ insumoSeleccionado.unidad_medida === 'mL' ? ' - unidad base' : '' }}
							</option>
							<option value="gal">
								Galón (gal){{ insumoSeleccionado.unidad_medida === 'gal' ? ' - unidad base' : '' }}
							</option>
							<option value="fl oz">
								Onza líquida (fl oz){{ insumoSeleccionado.unidad_medida === 'fl oz' ? ' - unidad base' : '' }}
							</option>
						</optgroup>
						<!-- Otras Unidades - Siempre visible -->
						<optgroup label="Unidades">
							<option value="unid">
								Unidad (unid){{ insumoSeleccionado.unidad_medida === 'unid' ? ' - unidad base' : '' }}
							</option>
							<option value="pza">
								Pieza (pza){{ insumoSeleccionado.unidad_medida === 'pza' ? ' - unidad base' : '' }}
							</option>
							<option value="caja">
								Caja{{ insumoSeleccionado.unidad_medida === 'caja' ? ' - unidad base' : '' }}
							</option>
							<option value="bolsa">
								Bolsa{{ insumoSeleccionado.unidad_medida === 'bolsa' ? ' - unidad base' : '' }}
							</option>
							<option value="paquete">
								Paquete{{ insumoSeleccionado.unidad_medida === 'paquete' ? ' - unidad base' : '' }}
							</option>
							<option value="botella">
								Botella{{ insumoSeleccionado.unidad_medida === 'botella' ? ' - unidad base' : '' }}
							</option>
							<option value="lata">
								Lata{{ insumoSeleccionado.unidad_medida === 'lata' ? ' - unidad base' : '' }}
							</option>
							<option value="sobre">
								Sobre{{ insumoSeleccionado.unidad_medida === 'sobre' ? ' - unidad base' : '' }}
							</option>
							<option value="frasco">
								Frasco{{ insumoSeleccionado.unidad_medida === 'frasco' ? ' - unidad base' : '' }}
							</option>
							<option value="tubo">
								Tubo{{ insumoSeleccionado.unidad_medida === 'tubo' ? ' - unidad base' : '' }}
							</option>
						</optgroup>
					</template>
					<option v-else disabled value="">Seleccione un insumo primero</option>
				</select>
				<p v-if="insumoSeleccionado" class="text-xs text-gray-500 mt-1">
					Unidad base del insumo: <span class="font-semibold">{{ formatUnidad(insumoSeleccionado.unidad_medida) }}</span>
					<span class="text-gray-600 text-xs ml-2">
						✓ La conversión automática funciona entre unidades del mismo tipo (peso↔peso, volumen↔volumen)
					</span>
				</p>
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Fecha</label>
				<input v-model="form.fecha" type="date" class="w-full border p-2" />
			</div>
			<div>
				<label class="block text-sm text-gray-700 mb-1">Método de inventario</label>
				<input v-model="form.metodo_inventario" class="w-full border p-2" placeholder="Ej: PEPS, UEPS" />
			</div>
			<div class="col-span-6">
				<label class="block text-sm text-gray-700 mb-1">Observación</label>
				<input v-model="form.observacion" class="w-full border p-2" placeholder="Opcional" />
			</div>
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
						<th class="text-left p-2">Insumo</th>
						<th class="text-left p-2">Tipo</th>
						<th class="text-right p-2">Cantidad</th>
						<th class="text-left p-2">Método</th>
						<th class="text-left p-2">Observación</th>
						<th class="text-left p-2">Acciones</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="m in movimientos.data" :key="m.id" class="border-t">
						<td class="p-2">{{ m.fecha }}</td>
						<td class="p-2">{{ m.insumo?.nombre }}</td>
						<td class="p-2">{{ m.tipo_movimiento }}</td>
						<td class="p-2 text-right">{{ m.cantidad }}</td>
						<td class="p-2">{{ m.metodo_inventario }}</td>
						<td class="p-2">{{ m.observacion }}</td>
						<td class="p-2">
							<button class="text-blue-600 mr-3" @click="startEdit(m)">Editar</button>
							<button class="text-red-600" @click="remove(m.id)">Eliminar</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AdminLayout from '../../Layouts/AdminLayout.vue'
const props = defineProps({
	movimientos: Object,
	insumos: Array,
})
const movimientos = props.movimientos
const insumos = props.insumos
defineOptions({ layout: AdminLayout })

const today = new Date().toISOString().slice(0, 10)
const form = useForm({
	insumo_id: '',
	tipo_movimiento: 'Entrada',
	cantidad: 0,
	unidad_movimiento: '',
	fecha: today,
	metodo_inventario: '',
	observacion: '',
})

// Encontrar el insumo seleccionado
const insumoSeleccionado = computed(() => {
	if (!form.insumo_id) return null
	return insumos.find(i => Number(i.id) === Number(form.insumo_id))
})


// Cuando cambia el insumo, establecer la unidad base por defecto
watch(() => form.insumo_id, (newId) => {
	if (insumoSeleccionado.value) {
		form.unidad_movimiento = insumoSeleccionado.value.unidad_medida
	}
})

function submit() {
	if (editingId.value) {
		form.put(`/inventario/${editingId.value}`, { onSuccess: () => cancelEdit() })
	} else {
		form.post('/inventario', {
			onSuccess: () => {
				form.reset({ fecha: today, tipo_movimiento: 'Entrada', unidad_movimiento: '' })
				if (insumoSeleccionado.value) {
					form.unidad_movimiento = insumoSeleccionado.value.unidad_medida
				}
			}
		})
	}
}
const editingId = ref(null)
function startEdit(m) {
	editingId.value = m.id
	form.insumo_id = m.insumo_id
	form.tipo_movimiento = m.tipo_movimiento
	form.cantidad = m.cantidad
	// Al editar, usar la unidad base del insumo (la cantidad ya está convertida)
	if (insumoSeleccionado.value) {
		form.unidad_movimiento = insumoSeleccionado.value.unidad_medida
	}
	form.fecha = m.fecha
	form.metodo_inventario = m.metodo_inventario ?? ''
	form.observacion = m.observacion ?? ''
}
function cancelEdit() {
	editingId.value = null
	form.reset({ fecha: today, tipo_movimiento: 'Entrada', unidad_movimiento: '' })
	if (insumoSeleccionado.value) {
		form.unidad_movimiento = insumoSeleccionado.value.unidad_medida
	}
}
function remove(id) {
	if (confirm('¿Eliminar movimiento?')) {
		router.delete(`/inventario/${id}`)
	}
}

function formatUnidad(unidad) {
	if (unidad === 'arroba') return 'ar'
	return unidad
}
</script>


