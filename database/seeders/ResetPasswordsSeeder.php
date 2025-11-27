<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ResetPasswordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = User::all();
        $password = 'password123';

        foreach ($usuarios as $usuario) {
            $usuario->password = Hash::make($password);
            $usuario->save();
            $this->command->info("Contraseña actualizada para: {$usuario->email} ({$usuario->name})");
        }

        $this->command->info('');
        $this->command->info("Todas las contraseñas han sido establecidas a: {$password}");
        $this->command->info('Ahora todos los usuarios pueden iniciar sesión con su email y la contraseña: password123');
    }
}

