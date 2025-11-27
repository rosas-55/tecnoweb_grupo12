<template>
	<div>
		<section class="mb-8">
			<h1 class="text-3xl font-bold">Sistema de Inventario y Producción</h1>
			<p class="text-gray-600">Restaurante Las Brazas</p>
		</section>

		<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
			<a href="/insumos" class="group bg-white rounded-xl border hover:shadow-md transition p-6">
				<div class="flex items-center gap-4">
					<div class="w-10 h-10 bg-blue-100 text-blue-700 grid place-content-center rounded-lg font-semibold">IN</div>
					<div>
						<h3 class="font-semibold group-hover:text-blue-700">Insumos</h3>
						<p class="text-sm text-gray-600">Gestión de materias primas</p>
					</div>
				</div>
			</a>
			<a href="/recetas" class="group bg-white rounded-xl border hover:shadow-md transition p-6">
				<div class="flex items-center gap-4">
					<div class="w-10 h-10 bg-emerald-100 text-emerald-700 grid place-content-center rounded-lg font-semibold">RC</div>
					<div>
						<h3 class="font-semibold group-hover:text-emerald-700">Recetas</h3>
						<p class="text-sm text-gray-600">Composición y tiempos</p>
					</div>
				</div>
			</a>
			<a href="/produccion" class="group bg-white rounded-xl border hover:shadow-md transition p-6">
				<div class="flex items-center gap-4">
					<div class="w-10 h-10 bg-amber-100 text-amber-700 grid place-content-center rounded-lg font-semibold">PR</div>
					<div>
						<h3 class="font-semibold group-hover:text-amber-700">Producción</h3>
						<p class="text-sm text-gray-600">Registro de lotes</p>
					</div>
				</div>
			</a>
		</section>
	</div>
</template>

<script setup>
import AdminLayout from '../Layouts/AdminLayout.vue'
defineOptions({ layout: AdminLayout })
</script>


