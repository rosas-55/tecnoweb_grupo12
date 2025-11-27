<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Propietario
        $propietario = User::firstOrCreate(
            ['email' => 'propietario@lasbrazas.com'],
            [
                'name' => 'Propietario Principal',
                'cedula' => '1234567890',
                'celular' => '3001234567',
                'direccion' => 'Calle Principal #123',
                'password' => Hash::make('propietario123')
            ]
        );
        $propietario->roles()->sync([Role::where('nombre', 'Propietario')->first()->id]);

        // Usuario Secretaria
        $secretaria = User::firstOrCreate(
            ['email' => 'secretaria@lasbrazas.com'],
            [
                'name' => 'María García',
                'cedula' => '9876543210',
                'celular' => '3009876543',
                'direccion' => 'Carrera 45 #12-34',
                'password' => Hash::make('secretaria123')
            ]
        );
        $secretaria->roles()->sync([Role::where('nombre', 'Secretaria')->first()->id]);

        // Usuario Proveedores
        $proveedor = User::firstOrCreate(
            ['email' => 'proveedor@lasbrazas.com'],
            [
                'name' => 'Juan Pérez - Proveedor',
                'cedula' => '5555555555',
                'celular' => '3005555555',
                'direccion' => 'Avenida 68 #88-99',
                'password' => Hash::make('proveedor123')
            ]
        );
        $proveedor->roles()->sync([Role::where('nombre', 'Proveedores')->first()->id]);

        // Usuario Clientes
        $cliente = User::firstOrCreate(
            ['email' => 'cliente@lasbrazas.com'],
            [
                'name' => 'Ana López',
                'cedula' => '1111111111',
                'celular' => '3001111111',
                'direccion' => 'Diagonal 70 #45-23',
                'password' => Hash::make('cliente123')
            ]
        );
        $cliente->roles()->sync([Role::where('nombre', 'Clientes')->first()->id]);

        $this->command->info('Usuarios de prueba creados exitosamente.');
        $this->command->info('');
        $this->command->info('Credenciales:');
        $this->command->info('Propietario: propietario@lasbrazas.com / propietario123');
        $this->command->info('Secretaria: secretaria@lasbrazas.com / secretaria123');
        $this->command->info('Proveedores: proveedor@lasbrazas.com / proveedor123');
        $this->command->info('Clientes: cliente@lasbrazas.com / cliente123');
    }
}
