<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

class Role extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Usuarios que tienen este rol
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_users');
    }

    /**
     * Permisos asignados a este rol
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * Verificar si el rol tiene un permiso específico
     * Versión segura que funciona aunque no exista la tabla permissions
     */
    public function hasPermission(string $permission): bool
    {
        try {
            // Verificar si la tabla permissions existe
            if (!Schema::hasTable('permissions')) {
                return false; // Si no existe, retornar false (se maneja por roles en User)
            }

            return $this->permissions()->where('name', $permission)->orWhere('slug', $permission)->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
}
