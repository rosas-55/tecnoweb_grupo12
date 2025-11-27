<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Usuarios
            ['name' => 'usuarios.create', 'slug' => 'usuarios-crear', 'description' => 'Crear usuarios'],
            ['name' => 'usuarios.read', 'slug' => 'usuarios-ver', 'description' => 'Ver usuarios'],
            ['name' => 'usuarios.update', 'slug' => 'usuarios-editar', 'description' => 'Editar usuarios'],
            ['name' => 'usuarios.delete', 'slug' => 'usuarios-eliminar', 'description' => 'Eliminar usuarios'],

            // Insumos
            ['name' => 'insumos.create', 'slug' => 'insumos-crear', 'description' => 'Crear insumos'],
            ['name' => 'insumos.read', 'slug' => 'insumos-ver', 'description' => 'Ver insumos'],
            ['name' => 'insumos.update', 'slug' => 'insumos-editar', 'description' => 'Editar insumos'],
            ['name' => 'insumos.delete', 'slug' => 'insumos-eliminar', 'description' => 'Eliminar insumos'],

            // Recetas
            ['name' => 'recetas.create', 'slug' => 'recetas-crear', 'description' => 'Crear recetas'],
            ['name' => 'recetas.read', 'slug' => 'recetas-ver', 'description' => 'Ver recetas'],
            ['name' => 'recetas.update', 'slug' => 'recetas-editar', 'description' => 'Editar recetas'],
            ['name' => 'recetas.delete', 'slug' => 'recetas-eliminar', 'description' => 'Eliminar recetas'],

            // Inventarios
            ['name' => 'inventarios.create', 'slug' => 'inventarios-crear', 'description' => 'Crear movimientos de inventario'],
            ['name' => 'inventarios.read', 'slug' => 'inventarios-ver', 'description' => 'Ver inventarios'],
            ['name' => 'inventarios.update', 'slug' => 'inventarios-editar', 'description' => 'Editar inventarios'],
            ['name' => 'inventarios.delete', 'slug' => 'inventarios-eliminar', 'description' => 'Eliminar inventarios'],

            // Producción
            ['name' => 'produccion.create', 'slug' => 'produccion-crear', 'description' => 'Crear producción'],
            ['name' => 'produccion.read', 'slug' => 'produccion-ver', 'description' => 'Ver producción'],
            ['name' => 'produccion.update', 'slug' => 'produccion-editar', 'description' => 'Editar producción'],
            ['name' => 'produccion.delete', 'slug' => 'produccion-eliminar', 'description' => 'Eliminar producción'],

            // Ventas
            ['name' => 'ventas.create', 'slug' => 'ventas-crear', 'description' => 'Crear ventas'],
            ['name' => 'ventas.read', 'slug' => 'ventas-ver', 'description' => 'Ver ventas'],
            ['name' => 'ventas.update', 'slug' => 'ventas-editar', 'description' => 'Editar ventas'],
            ['name' => 'ventas.delete', 'slug' => 'ventas-eliminar', 'description' => 'Eliminar ventas'],

            // Pagos
            ['name' => 'pagos.create', 'slug' => 'pagos-crear', 'description' => 'Crear pagos'],
            ['name' => 'pagos.read', 'slug' => 'pagos-ver', 'description' => 'Ver pagos'],
            ['name' => 'pagos.update', 'slug' => 'pagos-editar', 'description' => 'Editar pagos'],
            ['name' => 'pagos.delete', 'slug' => 'pagos-eliminar', 'description' => 'Eliminar pagos'],

            // Reportes
            ['name' => 'reportes.read', 'slug' => 'reportes-ver', 'description' => 'Ver reportes'],
            ['name' => 'reportes.generate', 'slug' => 'reportes-generar', 'description' => 'Generar reportes'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Asignar permisos a roles
        $this->asignarPermisosARoles();

        $this->command->info('Permisos creados y asignados exitosamente.');
    }

    protected function asignarPermisosARoles(): void
    {
        // Propietario - Todos los permisos (CRUD completo en todas las secciones)
        $propietario = \App\Models\Role::where('nombre', 'Propietario')->first();
        if ($propietario) {
            $todosLosPermisos = Permission::all();
            $propietario->permissions()->sync($todosLosPermisos->pluck('id'));
        }

        // Secretaria - Permisos operativos (Ver todo, crear/editar inventarios, producción, ventas y pagos)
        $secretaria = \App\Models\Role::where('nombre', 'Secretaria')->first();
        if ($secretaria) {
            $permisosSecretaria = Permission::whereIn('name', [
                'usuarios.read',
                'insumos.read',
                'recetas.read',
                'inventarios.create',
                'inventarios.read',
                'inventarios.update',
                'produccion.create',
                'produccion.read',
                'produccion.update',
                'ventas.create',
                'ventas.read',
                'ventas.update',
                'pagos.create',
                'pagos.read',
                'pagos.update',
                'reportes.read'
            ])->get();
            $secretaria->permissions()->sync($permisosSecretaria->pluck('id'));
        }

        // Proveedores - Solo lectura de insumos e inventarios
        $proveedores = \App\Models\Role::where('nombre', 'Proveedores')->first();
        if ($proveedores) {
            $permisosProveedores = Permission::whereIn('name', [
                'insumos.read',
                'inventarios.read',
                'reportes.read'
            ])->get();
            $proveedores->permissions()->sync($permisosProveedores->pluck('id'));
        }

        // Clientes - Solo ver recetas (menú), sus ventas y crear/ver sus pagos
        $clientes = \App\Models\Role::where('nombre', 'Clientes')->first();
        if ($clientes) {
            $permisosClientes = Permission::whereIn('name', [
                'recetas.read',
                'ventas.read',
                'pagos.read',
                'pagos.create'
            ])->get();
            $clientes->permissions()->sync($permisosClientes->pluck('id'));
        }
    }
}
