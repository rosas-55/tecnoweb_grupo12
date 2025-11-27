import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Composable para verificar permisos del usuario
 */
export function usePermissions() {
    const page = usePage();

    const permissions = computed(() => {
        return page.props.auth?.user?.permissions || [];
    });

    const hasPermission = (permission) => {
        if (!permission) return true;
        return permissions.value.includes(permission);
    };

    const hasAnyPermission = (permissionList) => {
        if (!permissionList || permissionList.length === 0) return true;
        return permissionList.some(perm => permissions.value.includes(perm));
    };

    const hasAllPermissions = (permissionList) => {
        if (!permissionList || permissionList.length === 0) return true;
        return permissionList.every(perm => permissions.value.includes(perm));
    };

    return {
        permissions,
        hasPermission,
        hasAnyPermission,
        hasAllPermissions
    };
}

