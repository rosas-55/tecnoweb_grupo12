<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Propietario',
                'descripcion' => 'Acceso completo al sistema',
            ],
            [
                'nombre' => 'Secretaria',
                'descripcion' => 'Gestión de ventas, inventario y producción',
            ],
            [
                'nombre' => 'Proveedores',
                'descripcion' => 'Visualización de inventario e insumos',
            ],
            [
                'nombre' => 'Clientes',
                'descripcion' => 'Visualización de ventas y pagos propios',
            ],
        ];

        foreach ($roles as $role) {
            $r = Role::firstOrCreate(
                ['nombre' => $role['nombre']],
                $role
            );
        }

        $this->command->info('Roles creados exitosamente. Ejecute PermissionSeeder para asignar permisos.');
    }
}
