<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';

const page = usePage();
const sidebarOpen = ref(true);
const searchQuery = ref('');
const currentTheme = ref('default');
const fontSize = ref(16); // Tamaño base en pixeles
const highContrast = ref(false);
const themeMenuOpen = ref(false);
const pageVisits = ref(0);
let searchTimeout = null;

// Props
const props = defineProps({
    title: String,
    searchPlaceholder: {
        type: String,
        default: 'Buscar...'
    },
    onSearch: Function
});

// Computed
const user = computed(() => page.props.auth.user);
const permissions = computed(() => {
    const userPerms = page.props.auth.user?.permissions || [];
    // Asegurar que siempre sea un array
    const permsArray = Array.isArray(userPerms) ? userPerms : [];

    // Debug: mostrar en consola qué permisos tiene el usuario (SIEMPRE VISIBLE)
    console.log('═══════════════════════════════════════════════════════');
    console.log('🔐 VERIFICACIÓN DE PERMISOS');
    console.log('═══════════════════════════════════════════════════════');
    console.log('👤 Usuario:', user.value?.email);
    console.log('🎭 Roles:', user.value?.roles);
    console.log('📋 Permisos (' + permsArray.length + '):', permsArray);
    console.log('═══════════════════════════════════════════════════════');

    return permsArray;
});

// Menú de navegación con permisos
const menuItems = computed(() => {
    const items = [
        {
            name: 'Dashboard',
            icon: '📊',
            route: 'dashboard',
            url: '/dashboard',
            permission: 'dashboard.read'
        },
        {
            name: 'Usuarios',
            icon: '👥',
            route: 'usuarios',
            url: '/usuarios',
            permission: 'usuarios.read'
        },
        {
            name: 'Insumos',
            icon: '📦',
            route: 'insumos',
            url: '/insumos',
            permission: 'insumos.read'
        },
        {
            name: 'Recetas',
            icon: '📝',
            route: 'recetas',
            url: '/recetas',
            permission: 'recetas.read'
        },
        {
            name: 'Inventario',
            icon: '📋',
            route: 'inventarios',
            url: '/inventarios',
            permission: 'inventarios.read'
        },
        {
            name: 'Producción',
            icon: '🏭',
            route: 'produccion',
            url: '/produccion',
            permission: 'produccion.read'
        },
        {
            name: 'Ventas',
            icon: '💰',
            route: 'ventas',
            url: '/ventas',
            permission: 'ventas.read'
        },
        {
            name: 'Pagos',
            icon: '💳',
            route: 'pagos',
            url: '/pagos',
            permission: 'pagos.read'
        },
        {
            name: 'Reportes',
            icon: '📈',
            route: 'reportes',
            url: '/reportes',
            permission: 'reportes.read'
        }
    ];

    // Filtrar por permisos - Verificar que el usuario tenga el permiso necesario
    const filteredItems = items.filter(item => {
        // No mostrar si no está autenticado
        if (!user.value) {
            console.log(`❌ ${item.name}: Usuario no autenticado`);
            return false;
        }

        // Obtener permisos del usuario
        const userPerms = permissions.value || [];

        // Si no hay permisos, no mostrar nada
        if (userPerms.length === 0) {
            console.log(`❌ ${item.name}: Usuario sin permisos`);
            return false;
        }

        // Verificar si el usuario tiene el permiso necesario
        if (!item.permission) {
            console.log(`❌ ${item.name}: No tiene permiso definido`);
            return false; // Si no tiene permiso definido, no mostrar
        }

        const hasPermission = userPerms.includes(item.permission);

        if (!hasPermission) {
            console.log(`❌ ${item.name}: NO tiene permiso "${item.permission}"`);
            console.log(`   Permisos disponibles:`, userPerms);
        } else {
            console.log(`✅ ${item.name}: Tiene permiso "${item.permission}"`);
        }

        return hasPermission;
    });

    console.log('═══════════════════════════════════════════════════════');
    console.log('📋 RESUMEN DEL MENÚ FILTRADO');
    console.log('═══════════════════════════════════════════════════════');
    console.log('Total items:', items.length);
    console.log('Items filtrados:', filteredItems.length);
    console.log('Items mostrados:', filteredItems.map(i => i.name));
    console.log('═══════════════════════════════════════════════════════');

    return filteredItems;
});

// Temas disponibles
const themes = [
    { value: 'default', label: 'Clásico', icon: '🌙', colors: 'bg-linear-to-br from-gray-800 to-gray-900 text-white' },
    { value: 'light', label: 'Día', icon: '☀️', colors: 'bg-linear-to-br from-blue-50 to-white text-gray-900' },
    { value: 'dark', label: 'Noche', icon: '🌑', colors: 'bg-linear-to-br from-gray-950 to-black text-gray-100' },
    { value: 'kids', label: 'Niños', icon: '🎈', colors: 'bg-linear-to-br from-pink-500 to-pink-600 text-white' },
    { value: 'teens', label: 'Jóvenes', icon: '🎮', colors: 'bg-linear-to-br from-purple-600 to-purple-700 text-white' }
];

// Tamaños de fuente con incrementos dinámicos
const increaseFontSize = () => {
    fontSize.value += 2;
    localStorage.setItem('fontSize', fontSize.value);
    applyFontSize();
};

const decreaseFontSize = () => {
    if (fontSize.value > 8) { // Límite mínimo de 8px
        fontSize.value -= 2;
        localStorage.setItem('fontSize', fontSize.value);
        applyFontSize();
    }
};

// Métodos
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const handleSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
        if (props.onSearch) {
            props.onSearch(searchQuery.value);
        }
    }, 300); // Espera 300ms después de que el usuario deje de escribir
};

const changeTheme = (theme) => {
    currentTheme.value = theme;
    localStorage.setItem('theme', theme);
    applyTheme();
};

const applyTheme = () => {
    const root = document.documentElement;
    root.setAttribute('data-theme', currentTheme.value);

    // Aplicar colores según el tema
    const themeColors = {
        default: { bg: '#1f2937', text: '#ffffff', accent: '#3b82f6' },
        kids: { bg: '#ec4899', text: '#ffffff', accent: '#fbbf24' },
        teens: { bg: '#7c3aed', text: '#ffffff', accent: '#10b981' },
        light: { bg: '#ffffff', text: '#1f2937', accent: '#3b82f6' },
        dark: { bg: '#030712', text: '#f3f4f6', accent: '#6366f1' }
    };

    const colors = themeColors[currentTheme.value];
    root.style.setProperty('--color-bg', colors.bg);
    root.style.setProperty('--color-text', colors.text);
    root.style.setProperty('--color-accent', colors.accent);
};

const applyFontSize = () => {
    const root = document.documentElement;
    root.style.setProperty('--font-size-base', `${fontSize.value}px`);
};

const applyContrast = () => {
    const root = document.documentElement;
    if (highContrast.value) {
        root.classList.add('high-contrast');
    } else {
        root.classList.remove('high-contrast');
    }
};

const logout = () => {
    router.post('/logout');
};

// Contador de visitas
const incrementPageVisit = () => {
    const currentPage = window.location.pathname;
    const visitKey = `page_visits_${currentPage}`;
    const visits = parseInt(localStorage.getItem(visitKey) || '0');
    const newVisits = visits + 1;
    localStorage.setItem(visitKey, newVisits.toString());
    pageVisits.value = newVisits;
};

const getTotalVisits = () => {
    let total = 0;
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith('page_visits_')) {
            total += parseInt(localStorage.getItem(key) || '0');
        }
    }
    return total;
};

// Lifecycle
onMounted(() => {
    // Cargar preferencias guardadas
    currentTheme.value = localStorage.getItem('theme') || 'default';
    fontSize.value = parseInt(localStorage.getItem('fontSize')) || 16;

    applyTheme();
    applyFontSize();

    // Incrementar contador de visitas
    incrementPageVisit();
});

// Watch para actualizar visitas al cambiar de página
watch(() => page.url, () => {
    incrementPageVisit();
});
</script>

<template>
    <div class="min-h-screen" :class="`theme-${currentTheme}`">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed top-0 left-0 z-40 h-screen transition-transform',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'w-64 bg-sidebar'
            ]"
        >
            <div class="h-full px-3 py-4 overflow-y-auto">
                <!-- Logo -->
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-xl font-bold text-primary">🔥 Las Brazas</h1>
                    <button @click="toggleSidebar" class="lg:hidden p-2 hover:bg-accent rounded-lg">
                        ✕
                    </button>
                </div>

                <!-- Usuario -->
                <div class="mb-6 p-3 rounded-lg bg-accent/10">
                    <p class="text-sm font-semibold text-primary">{{ user?.name }}</p>
                    <p class="text-xs text-secondary">{{ user?.email }}</p>
                </div>

                <!-- Menú de navegación -->
                <nav class="space-y-2">
                    <Link
                        v-for="item in menuItems"
                        :key="item.route"
                        :href="item.url"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors',
                            page.url.startsWith(item.url)
                                ? 'bg-accent text-white'
                                : 'text-secondary hover:bg-accent/20'
                        ]"
                    >
                        <span class="text-xl">{{ item.icon }}</span>
                        <span class="font-medium">{{ item.name }}</span>
                    </Link>
                </nav>

                <!-- Configuración de accesibilidad -->
                <div class="mt-6 pt-6 border-t border-accent/20">
                    <!-- Tamaño de fuente -->
                    <div class="mb-5">
                        <label class="text-xs text-secondary font-semibold block mb-3 uppercase tracking-wide">
                            📝 Tamaño de Texto
                        </label>
                        <div class="bg-accent/10 rounded-xl p-4 border border-accent/30">
                            <div class="flex items-center justify-between gap-4">
                                <button
                                    @click="decreaseFontSize"
                                    :disabled="fontSize <= 8"
                                    :class="[
                                        'w-11 h-11 rounded-full font-bold text-base transition-all duration-200 shadow-md flex items-center justify-center',
                                        fontSize <= 8
                                            ? 'bg-gray-400 text-gray-600 cursor-not-allowed opacity-50'
                                            : 'bg-accent text-white hover:bg-accent/80 hover:shadow-lg hover:scale-110'
                                    ]"
                                    title="Reducir texto (2px)"
                                >
                                    A-
                                </button>

                                <div class="flex-1 text-center bg-white/50 rounded-lg py-2 px-3">
                                    <p class="text-2xl font-bold text-accent">{{ fontSize }}px</p>
                                    <p class="text-xs text-secondary mt-0.5">Tamaño actual</p>
                                </div>

                                <button
                                    @click="increaseFontSize"
                                    :class="[
                                        'w-11 h-11 rounded-full font-bold text-base transition-all duration-200 shadow-md flex items-center justify-center',
                                        'bg-accent text-white hover:bg-accent/80 hover:shadow-lg hover:scale-110'
                                    ]"
                                    title="Aumentar texto (2px)"
                                >
                                    A+
                                </button>
                            </div>
                            <div class="mt-3 flex gap-1">
                                <button
                                    @click="fontSize = 12; applyFontSize(); localStorage.setItem('fontSize', 12)"
                                    class="flex-1 px-2 py-1 text-xs bg-white/30 hover:bg-white/50 rounded transition-colors"
                                    :class="fontSize === 12 ? 'ring-2 ring-accent' : ''"
                                >
                                    Pequeño
                                </button>
                                <button
                                    @click="fontSize = 16; applyFontSize(); localStorage.setItem('fontSize', 16)"
                                    class="flex-1 px-2 py-1 text-xs bg-white/30 hover:bg-white/50 rounded transition-colors"
                                    :class="fontSize === 16 ? 'ring-2 ring-accent' : ''"
                                >
                                    Normal
                                </button>
                                <button
                                    @click="fontSize = 20; applyFontSize(); localStorage.setItem('fontSize', 20)"
                                    class="flex-1 px-2 py-1 text-xs bg-white/30 hover:bg-white/50 rounded transition-colors"
                                    :class="fontSize === 20 ? 'ring-2 ring-accent' : ''"
                                >
                                    Grande
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logout -->
                <button
                    @click="logout"
                    class="w-full mt-6 flex items-center gap-3 px-3 py-2 rounded-lg text-red-500 hover:bg-red-500/10 transition-colors"
                >
                    <span class="text-xl">🚪</span>
                    <span class="font-medium">Cerrar Sesión</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div :class="['transition-all', sidebarOpen ? 'lg:ml-64' : '']">
            <!-- Header -->
            <header class="sticky top-0 z-30 bg-header border-b border-accent/20">
                <div class="px-4 py-3 lg:px-6">
                    <div class="flex items-center justify-between">
                        <!-- Toggle sidebar + Title -->
                        <div class="flex items-center gap-4">
                            <button
                                @click="toggleSidebar"
                                class="p-2 hover:bg-accent/20 rounded-lg transition-colors"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <h2 class="text-xl font-bold text-primary">{{ title }}</h2>
                        </div>

                        <!-- Buscador -->
                        <div class="flex-1 max-w-md mx-4">
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    :placeholder="searchPlaceholder"
                                    class="w-full px-4 py-2 pl-10 pr-10 rounded-lg bg-accent/10 text-primary border border-accent/30 focus:outline-none focus:border-accent"
                                    @input="handleSearch"
                                >
                                <svg class="absolute left-3 top-2.5 w-5 h-5 text-secondary pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <button
                                    v-if="searchQuery"
                                    @click="searchQuery = ''"
                                    class="absolute right-3 top-2.5 w-5 h-5 text-secondary hover:text-primary transition-colors"
                                    title="Limpiar búsqueda"
                                >
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Selector de Tema y Fecha -->
                        <div class="flex items-center gap-3">
                            <!-- Selector de Tema -->
                            <div class="relative">
                                <button
                                    @click="themeMenuOpen = !themeMenuOpen"
                                    class="p-2 hover:bg-accent/20 rounded-lg transition-colors flex items-center gap-2"
                                    :title="`Tema: ${themes.find(t => t.value === currentTheme)?.label || 'Clásico'}`"
                                >
                                    <span class="text-2xl">{{ themes.find(t => t.value === currentTheme)?.icon || '🎨' }}</span>
                                </button>

                                <div
                                    v-show="themeMenuOpen"
                                    class="absolute right-0 top-full mt-2 w-48 bg-sidebar rounded-xl shadow-2xl border-2 border-accent/30 overflow-hidden z-50 animate-slideDown"
                                >
                                    <div class="p-2 space-y-1">
                                        <button
                                            v-for="theme in themes"
                                            :key="theme.value"
                                            @click="changeTheme(theme.value); themeMenuOpen = false"
                                            :class="[
                                                'w-full relative px-3 py-2 rounded-lg text-sm font-bold transition-all duration-200 flex items-center gap-3',
                                                currentTheme === theme.value
                                                    ? 'ring-2 ring-white ring-offset-1 ring-offset-sidebar'
                                                    : 'opacity-70 hover:opacity-100',
                                                theme.colors
                                            ]"
                                        >
                                            <span class="text-lg">{{ theme.icon }}</span>
                                            <span class="flex-1 text-left">{{ theme.label }}</span>
                                            <span
                                                v-if="currentTheme === theme.value"
                                                class="text-white text-sm"
                                            >
                                                ✓
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha -->
                            <div class="text-sm text-secondary">
                                {{ new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 lg:p-6 bg-body min-h-[calc(100vh-140px)]">
                <slot />

                <!-- Footer con contador de visitas - Fijo en la parte inferior -->
                <div class="fixed bottom-0 left-0 right-0 bg-header/95 backdrop-blur-sm border-t border-accent/20 py-2 px-6 z-20" :class="sidebarOpen ? 'lg:ml-64' : ''">
                    <div class="flex justify-between items-center text-xs text-secondary">
                        <div>
                            © 2025 Restaurante Las Brazas
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-2">
                                <span>👁️ Visitas de esta página:</span>
                                <span class="font-bold text-accent">{{ pageVisits }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span>📊 Total de visitas:</span>
                                <span class="font-bold text-accent">{{ getTotalVisits() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style>
:root {
    --color-bg: #1f2937;
    --color-text: #ffffff;
    --color-accent: #3b82f6;
    --font-size-base: 16px;
}

/* Temas con mejor contraste y legibilidad */
.theme-default {
    --color-primary: #f9fafb;
    --color-secondary: #d1d5db;
    --color-sidebar: #1f2937;
    --color-header: #111827;
    --color-body: #111827;
    --color-accent: #3b82f6;
}

.theme-kids {
    --color-primary: #ffffff;
    --color-secondary: #fce7f3;
    --color-sidebar: #ec4899;
    --color-header: #db2777;
    --color-body: #fef3f9;
    --color-accent: #fbbf24;
}

.theme-teens {
    --color-primary: #ffffff;
    --color-secondary: #ede9fe;
    --color-sidebar: #7c3aed;
    --color-header: #6d28d9;
    --color-body: #faf5ff;
    --color-accent: #10b981;
}

.theme-light {
    --color-primary: #111827;
    --color-secondary: #4b5563;
    --color-sidebar: #f9fafb;
    --color-header: #ffffff;
    --color-body: #f3f4f6;
    --color-accent: #3b82f6;
}

.theme-dark {
    --color-primary: #f9fafb;
    --color-secondary: #d1d5db;
    --color-sidebar: #030712;
    --color-header: #0c0a09;
    --color-body: #0c0a09;
    --color-accent: #818cf8;
}

/* Colores aplicados con mejor contraste */
.text-primary {
    color: var(--color-primary);
    font-weight: 500;
    letter-spacing: 0.01em;
}

.text-secondary {
    color: var(--color-secondary);
    font-weight: 400;
}

.bg-sidebar {
    background-color: var(--color-sidebar);
}

.bg-header {
    background-color: var(--color-header);
    backdrop-filter: blur(10px);
}

.bg-body {
    background-color: var(--color-body);
}

.bg-accent {
    background-color: var(--color-accent);
}

/* Mejorar legibilidad de inputs y selects */
input, select, textarea, button {
    color: var(--color-primary) !important;
    font-weight: 500;
}

/* Mejorar contraste de enlaces y botones */
a, button {
    font-weight: 500;
    letter-spacing: 0.01em;
}

/* Alto contraste */
.high-contrast {
    --color-primary: #000000;
    --color-secondary: #000000;
    --color-bg: #ffffff;
    --color-accent: #0000ff;
}

.high-contrast .bg-sidebar,
.high-contrast .bg-header,
.high-contrast .bg-body {
    background-color: #ffffff !important;
    border: 2px solid #000000;
}

.high-contrast .text-primary,
.high-contrast .text-secondary {
    color: #000000 !important;
    font-weight: 700;
}

/* Aplicar tamaño de fuente base dinámicamente */
body, html {
    font-size: var(--font-size-base, 16px);
}

/* Smooth transitions */
* {
    transition: background-color 0.3s ease, color 0.3s ease, font-size 0.3s ease;
}

/* Animación para dropdown */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slideDown {
    animation: slideDown 0.2s ease-out;
}
</style>
