<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Indica si el modelo debe usar timestamps
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'cedula',
        'celular',
        'direccion',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Roles del usuario
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users');
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('nombre', $role)->exists();
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('nombre', $roles)->exists();
    }

    /**
     * Obtener todos los permisos del usuario a través de sus roles
     * Versión segura que funciona aunque no exista la tabla permissions
     */
    public function permissions()
    {
        try {
            // Intentar cargar permisos desde la tabla permissions si existe
            $roles = $this->roles()->with('permissions')->get();
            if ($roles->isEmpty()) {
                return collect([]);
            }

            // Verificar si los roles tienen permisos cargados
            $permissions = $roles->pluck('permissions')->flatten();
            if ($permissions->isEmpty()) {
                // Si no hay permisos en la BD, retornar colección vacía
                return collect([]);
            }

            return $permissions->unique('id');
        } catch (\Exception $e) {
            // Si la tabla permissions no existe o hay error, retornar colección vacía
            return collect([]);
        }
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     * Versión segura que funciona aunque no exista la tabla permissions
     */
    public function hasPermission(string $permission): bool
    {
        try {
            // Primero verificar si la tabla permissions existe
            if (!Schema::hasTable('permissions')) {
                // Si no existe, usar lógica basada en roles
                return $this->hasPermissionByRole($permission);
            }

            // Si existe, usar la tabla permissions
            return $this->roles()
                ->whereHas('permissions', function ($query) use ($permission) {
                    $query->where('name', $permission)->orWhere('slug', $permission);
                })
                ->exists();
        } catch (\Exception $e) {
            // Si hay error, usar lógica basada en roles
            return $this->hasPermissionByRole($permission);
        }
    }

    /**
     * Verificar permiso basado en roles (sin usar tabla permissions)
     */
    protected function hasPermissionByRole(string $permission): bool
    {
        $roles = $this->roles->pluck('nombre')->toArray();

        // Si es Propietario, Administrador o Gerente (compatibilidad con BD existente), tiene todos los permisos
        $rolesLower = array_map('strtolower', $roles);
        if (in_array('propietario', $rolesLower) ||
            in_array('administrador', $rolesLower) ||
            in_array('gerente', $rolesLower)) {
            return true;
        }

        // Mapeo de permisos por rol (solo los 4 roles: Propietario, Secretaria, Proveedores, Clientes)
        $rolePermissions = [
            'secretaria' => ['usuarios.read', 'insumos.read', 'recetas.read', 'inventarios.read', 'inventarios.create', 'inventarios.update',
                           'produccion.read', 'produccion.create', 'produccion.update', 'ventas.read', 'ventas.create', 'ventas.update',
                           'pagos.read', 'pagos.create', 'pagos.update', 'reportes.read'],
            'proveedores' => ['insumos.read', 'inventarios.read', 'reportes.read'],
            'proveedor' => ['insumos.read', 'inventarios.read', 'reportes.read'], // Variante singular
            'clientes' => ['recetas.read', 'ventas.read', 'pagos.read', 'pagos.create'],
            'cliente' => ['recetas.read', 'ventas.read', 'pagos.read', 'pagos.create'], // Variante singular
        ];

        foreach ($roles as $role) {
            $roleLower = strtolower($role);
            if (isset($rolePermissions[$roleLower]) && in_array($permission, $rolePermissions[$roleLower])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos especificados
     * Versión segura que funciona aunque no exista la tabla permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ventas donde este usuario es el cliente
     */
    public function ventasComoCliente()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }

    /**
     * Ventas registradas por este usuario
     */
    public function ventasRegistradas()
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    /**
     * Producciones donde este usuario es el responsable
     */
    public function producciones()
    {
        return $this->hasMany(Produccion::class, 'responsable_id');
    }

    /**
     * Reportes generados por este usuario
     */
    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'generado_por');
    }
}
