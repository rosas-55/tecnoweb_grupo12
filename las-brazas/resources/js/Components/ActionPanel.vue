<template>
	<div class="bg-white border rounded mb-6">
		<div class="px-4 py-3 border-b">
			<h3 class="font-semibold">{{ title }}</h3>
			<p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
		</div>
		<ul>
			<li v-for="(a, i) in actions" :key="i" class="px-4 py-3 border-t flex items-center justify-between">
				<div>
					<div class="font-medium">{{ a.label }}</div>
					<div v-if="a.hint" class="text-xs text-gray-500 mt-0.5">{{ a.hint }}</div>
				</div>
				<div>
					<a v-if="a.to" :href="a.to" class="px-3 py-1.5 rounded text-sm border hover:bg-gray-50">Ir</a>
					<button v-else class="px-3 py-1.5 rounded text-sm border text-gray-400 cursor-not-allowed" title="Disponible en esta pantalla">
						Acción
					</button>
				</div>
			</li>
		</ul>
	</div>
	</template>

<script setup>
defineProps({
	title: String,
	subtitle: String,
	actions: { type: Array, default: () => [] },
})
</script>


