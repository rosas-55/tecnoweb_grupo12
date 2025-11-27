<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $this->getUserData($request->user()) : null,
            ],
        ];
    }

    /**
     * Obtener datos del usuario de forma segura
     * Funciona sin la tabla permissions (solo usa roles)
     */
    protected function getUserData($user): array
    {
        try {
            // Cargar roles de forma segura
            $roles = [];
            try {
                $userRoles = $user->roles;
                if ($userRoles && $userRoles->count() > 0) {
                    $roles = $userRoles->map(fn($role) => $role->nombre ?? '')->filter()->toArray();
                    Log::info('📋 Roles cargados del usuario', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'roles_count' => count($roles),
                        'roles' => $roles
                    ]);
                } else {
                    Log::warning('⚠️ Usuario sin roles asignados', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                }
            } catch (\Exception $e) {
                // Si hay error al cargar roles, usar array vacío
                Log::error('❌ Error al cargar roles del usuario', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                $roles = [];
            }

            // SIEMPRE generar permisos basados en roles (más confiable)
            // Esto asegura que los permisos se asignen correctamente según el rol
            $permissions = $this->generatePermissionsFromRoles($roles);

            // Log para debugging - SIEMPRE mostrar para ver qué está pasando
            Log::info('🔐 Permisos generados para usuario', [
                'user_id' => $user->id,
                'email' => $user->email,
                'roles' => $roles,
                'permissions_count' => count($permissions),
                'permissions' => $permissions
            ]);

            return [
                'id' => $user->id,
                'name' => $user->name ?? '',
                'email' => $user->email ?? '',
                'cedula' => $user->cedula ?? '',
                'celular' => $user->celular ?? '',
                'direccion' => $user->direccion ?? '',
                'roles' => $roles,
                'permissions' => $permissions,
            ];
        } catch (\Exception $e) {
            // Si hay cualquier error, retornar datos básicos sin roles/permisos
            Log::warning('Error al cargar datos del usuario en HandleInertiaRequests', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);

            return [
                'id' => $user->id ?? 0,
                'name' => $user->name ?? '',
                'email' => $user->email ?? '',
                'cedula' => $user->cedula ?? '',
                'celular' => $user->celular ?? '',
                'direccion' => $user->direccion ?? '',
                'roles' => [],
                'permissions' => [],
            ];
        }
    }

    /**
     * Generar permisos basados en roles (sin usar tabla permissions)
     * Esto permite que el sistema funcione aunque no exista la tabla permissions
     */
    protected function generatePermissionsFromRoles(array $roles): array
    {
        $permissions = [];

        // Normalizar roles: eliminar espacios, convertir a minúsculas y trim
        $rolesLower = array_map(function($role) {
            return strtolower(trim($role));
        }, $roles);
        $rolesOriginal = $roles; // Mantener originales para logs

        // Log detallado de normalización
        Log::info('🔍 Normalización de roles', [
            'roles_originales' => $rolesOriginal,
            'roles_normalizados' => $rolesLower,
            'count_original' => count($rolesOriginal),
            'count_normalizado' => count($rolesLower)
        ]);

        Log::info('🔍 Analizando roles para permisos', [
            'roles_originales' => $rolesOriginal,
            'roles_normalizados' => $rolesLower
        ]);

        // Si es Propietario, Administrador o Gerente (compatibilidad con BD existente), dar TODOS los permisos
        // IMPORTANTE: Verificar PRIMERO si es Propietario antes de otros roles
        $isPropietario = in_array('propietario', $rolesLower) ||
                         in_array('administrador', $rolesLower) ||
                         in_array('gerente', $rolesLower);

        if ($isPropietario) {
            Log::info('🔑 Usuario con rol de administrador - todos los permisos', [
                'roles' => $rolesOriginal,
                'roles_lower' => $rolesLower
            ]);
            return [
                'dashboard.read', // Dashboard: Ver todo
                'usuarios.read', 'usuarios.create', 'usuarios.update', 'usuarios.delete',
                'insumos.read', 'insumos.create', 'insumos.update', 'insumos.delete',
                'recetas.read', 'recetas.create', 'recetas.update', 'recetas.delete',
                'inventarios.read', 'inventarios.create', 'inventarios.update', 'inventarios.delete',
                'produccion.read', 'produccion.create', 'produccion.update', 'produccion.delete',
                'ventas.read', 'ventas.create', 'ventas.update', 'ventas.delete',
                'pagos.read', 'pagos.create', 'pagos.update', 'pagos.delete',
                'reportes.read', 'reportes.generate',
            ];
        }

        // Si es Secretaria (solo este rol existe, no "cajera" ni "secretario")
        $isSecretaria = in_array('secretaria', $rolesLower);

        if ($isSecretaria) {
            Log::info('✅ Detectado rol Secretaria - Asignando permisos', [
                'roles_originales' => $rolesOriginal,
                'roles_lower' => $rolesLower,
                'coincidencia' => 'secretaria encontrado en roles_lower',
                'is_secretaria' => true
            ]);
            // NO usar array_merge, asignar directamente para evitar problemas
            $permissions = [
                'dashboard.read', // Dashboard: Ver todo
                'usuarios.read', // Usuarios: Ver
                'insumos.read', // Insumos: Ver
                'recetas.read', // Recetas: Ver
                'inventarios.read', 'inventarios.create', 'inventarios.update', // Inventarios: Ver, Crear, Editar
                'produccion.read', 'produccion.create', 'produccion.update', // Producción: Ver, Crear, Editar
                'ventas.read', 'ventas.create', 'ventas.update', // Ventas: Ver, Crear, Editar
                'pagos.read', 'pagos.create', 'pagos.update', // Pagos: Ver, Crear, Editar
                'reportes.read', // Reportes: Ver
            ];
        } else {
            Log::warning('⚠️ Rol Secretaria NO detectado', [
                'roles_originales' => $rolesOriginal,
                'roles_lower' => $rolesLower,
                'buscando' => 'secretaria',
                'esta_en_array' => in_array('secretaria', $rolesLower) ? 'SÍ' : 'NO',
                'is_secretaria' => false
            ]);
        }

        // Si es Proveedores - Solo: Insumos (Ver), Inventarios (Ver), Reportes (Ver - solo inventario)
        // Dashboard: No accede
        $isProveedores = in_array('proveedores', $rolesLower) || in_array('proveedor', $rolesLower);

        if ($isProveedores && !$isSecretaria && !$isPropietario) {
            Log::info('✅ Detectado rol Proveedores - Asignando permisos', [
                'roles_originales' => $rolesOriginal,
                'roles_lower' => $rolesLower
            ]);
            // NO usar array_merge, asignar directamente para evitar problemas
            $permissions = [
                'insumos.read',
                'inventarios.read',
                'reportes.read',
                // NO incluir dashboard.read - Proveedores no accede al Dashboard
            ];
        } else if ($isProveedores) {
            Log::debug('⚠️ Rol Proveedores detectado pero ya tiene otros permisos', [
                'roles_originales' => $rolesOriginal,
                'roles_lower' => $rolesLower,
                'is_secretaria' => $isSecretaria,
                'is_propietario' => $isPropietario
            ]);
        }

        // Si es Clientes - Solo: Dashboard (Ver datos propios), Recetas (Ver menú), Ventas (Ver propias), Pagos (Ver/Crear propios)
        $isClientes = in_array('clientes', $rolesLower) || in_array('cliente', $rolesLower);

        if ($isClientes && !$isSecretaria && !$isPropietario && !$isProveedores) {
            Log::info('✅ Detectado rol Clientes', [
                'roles_originales' => $rolesOriginal,
                'roles_lower' => $rolesLower
            ]);
            // NO usar array_merge, asignar directamente para evitar problemas
            $permissions = [
                'dashboard.read', // Dashboard: Ver (solo datos propios)
                'recetas.read', // Recetas: Ver (para ver el menú)
                'ventas.read', // Ventas: Ver (solo sus propias ventas)
                'pagos.read', 'pagos.create', // Pagos: Ver, Crear (solo sus propios pagos)
            ];
        }

        $finalPermissions = array_unique($permissions);

        Log::info('📋 Permisos generados para roles', [
            'roles' => $rolesOriginal,
            'permissions_count' => count($finalPermissions),
            'permissions' => $finalPermissions
        ]);

        return $finalPermissions;
    }
}
