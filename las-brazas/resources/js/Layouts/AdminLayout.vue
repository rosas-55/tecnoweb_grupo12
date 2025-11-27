<template>
	<div class="min-h-screen bg-gray-50 text-gray-800 flex">
		<aside class="w-60 bg-[#3b1d1d] text-white flex flex-col">
			<div class="h-16 px-4 flex items-center gap-2 border-b border-white/10">
				<span class="inline-block w-2 h-6 bg-amber-500 rounded"></span>
				<span class="font-semibold">LAS BRAZAS</span>
			</div>
			<nav class="flex-1 p-2 text-sm">
				<SidebarLink href="/" label="Dashboard" />
				<SidebarLink href="/insumos" label="Insumos" />
				<SidebarLink href="/inventario" label="Inventario" />
				<SidebarLink href="/recetas" label="Recetas" />
				<SidebarLink href="/produccion" label="Producción" />
				<SidebarLink href="/ventas" label="Ventas" />
				<SidebarLink href="/pagos" label="Pagos" />
				<SidebarLink href="/roles" label="Roles" />
				<SidebarLink href="/users" label="Usuarios" />
			</nav>
			<div class="p-4 text-xs text-white/70">v0.1</div>
		</aside>
		<main class="flex-1">
			<header class="h-16 bg-white border-b flex items-center px-6">
				<h1 class="text-lg font-semibold"><slot name="title" /></h1>
			</header>
			<section class="p-6 max-w-6xl mx-auto">
				<slot />
			</section>
		</main>
	</div>
	</template>

<script setup>
import { h } from 'vue'
import { Link } from '@inertiajs/vue3'

const SidebarLink = (props) => {
	return h(
		Link,
		{
			href: props.href,
			class:
				'block px-3 py-2 rounded hover:bg-white/10 transition aria-[current=page]:bg-white/15',
			'aria-current': undefined,
		},
		() => props.label
	)
}
</script>


