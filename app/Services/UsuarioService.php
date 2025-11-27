<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UsuarioService
{
    public function paginate(?string $search = null, int $perPage = 15)
    {
        $query = User::with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        $usuario = User::create($data);

        if (isset($data['roles'])) {
            $usuario->roles()->sync($data['roles']);
        }

        return $usuario;
    }

    public function update(User $usuario, array $data): User
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        // Sincronizar roles - siempre actualizar, incluso si viene vacío
        if (isset($data['roles'])) {
            $usuario->roles()->sync($data['roles']);
        } else {
            // Si no viene roles en el request, eliminar todos los roles
            $usuario->roles()->sync([]);
        }

        // Recargar relaciones para asegurar que se actualicen
        $usuario->load('roles');

        return $usuario;
    }

    public function delete(User $usuario): bool
    {
        try {
            // Usar transacción para asegurar consistencia
            return DB::transaction(function () use ($usuario) {
                $userId = $usuario->id;

                Log::info('Iniciando eliminación de usuario en servicio', [
                    'usuario_id' => $userId,
                    'email' => $usuario->email
                ]);

                // 1. Eliminar primero las relaciones de roles
                $rolesCount = $usuario->roles()->count();
                $usuario->roles()->detach();
                Log::info('Roles eliminados', [
                    'usuario_id' => $userId,
                    'roles_eliminados' => $rolesCount
                ]);

                // 2. Limpiar sesiones del usuario (si la tabla existe)
                try {
                    if (DB::getSchemaBuilder()->hasTable('sessions')) {
                        $sessionsDeleted = DB::table('sessions')
                            ->where('user_id', $userId)
                            ->delete();
                        Log::info('Sesiones eliminadas', [
                            'usuario_id' => $userId,
                            'sesiones_eliminadas' => $sessionsDeleted
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('No se pudieron eliminar sesiones (tabla no existe o error)', [
                        'usuario_id' => $userId,
                        'error' => $e->getMessage()
                    ]);
                }

                // 3. Luego eliminar el usuario
                $deleted = $usuario->delete();

                if ($deleted) {
                    Log::info('Usuario eliminado exitosamente desde servicio', [
                        'usuario_id' => $userId
                    ]);
                } else {
                    Log::warning('Usuario no eliminado (delete() retornó false)', [
                        'usuario_id' => $userId
                    ]);
                }

                return $deleted;
            });
        } catch (\Exception $e) {
            Log::error('Error al eliminar usuario en servicio', [
                'usuario_id' => $usuario->id ?? null,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
