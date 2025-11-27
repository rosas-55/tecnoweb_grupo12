<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    protected $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $usuarios = $this->usuarioService->paginate($search);

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'search' => $search
        ]);
    }

    public function create()
    {
        // Solo mostrar los 4 roles: Propietario, Secretaria, Proveedores, Clientes
        // Buscar todos los roles y filtrar por nombre normalizado, agrupando duplicados
        $allRoles = Role::all();
        $allowedNames = ['propietario', 'secretaria', 'proveedores', 'clientes'];
        $rolesVistos = [];

        $roles = $allRoles->filter(function($role) use ($allowedNames, &$rolesVistos) {
            $nombreNormalizado = strtolower(trim($role->nombre));

            // Solo incluir si está en la lista permitida y no lo hemos visto antes
            if (in_array($nombreNormalizado, $allowedNames) && !in_array($nombreNormalizado, $rolesVistos)) {
                $rolesVistos[] = $nombreNormalizado;
                return true;
            }
            return false;
        })
        ->sortBy('nombre')
        ->values();

        return Inertia::render('Usuarios/Create', [
            'roles' => $roles
        ]);
    }

    public function store(StoreUsuarioRequest $request)
    {
        $usuario = $this->usuarioService->create($request->validated());

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $usuario)
    {
        // Solo mostrar los 4 roles: Propietario, Secretaria, Proveedores, Clientes
        // Buscar todos los roles y filtrar por nombre normalizado, agrupando duplicados
        $allRoles = Role::all();
        $allowedNames = ['propietario', 'secretaria', 'proveedores', 'clientes'];
        $rolesVistos = [];

        $roles = $allRoles->filter(function($role) use ($allowedNames, &$rolesVistos) {
            $nombreNormalizado = strtolower(trim($role->nombre));

            // Solo incluir si está en la lista permitida y no lo hemos visto antes
            if (in_array($nombreNormalizado, $allowedNames) && !in_array($nombreNormalizado, $rolesVistos)) {
                $rolesVistos[] = $nombreNormalizado;
                return true;
            }
            return false;
        })
        ->sortBy('nombre')
        ->values();

        $usuario->load('roles');

        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario,
            'roles' => $roles
        ]);
    }

    public function update(UpdateUsuarioRequest $request, User $usuario)
    {
        $this->usuarioService->update($usuario, $request->validated());

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario)
    {
        try {
            Log::info('Iniciando eliminación de usuario', [
                'usuario_id' => $usuario->id,
                'email' => $usuario->email
            ]);

            $deleted = $this->usuarioService->delete($usuario);

            if ($deleted) {
                Log::info('Usuario eliminado correctamente desde controller', [
                    'usuario_id' => $usuario->id
                ]);

                return redirect()->route('usuarios.index')
                    ->with('success', 'Usuario eliminado exitosamente.');
            } else {
                Log::warning('No se pudo eliminar el usuario (retornó false)', [
                    'usuario_id' => $usuario->id
                ]);

                return redirect()->route('usuarios.index')
                    ->with('error', 'No se pudo eliminar el usuario.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error de base de datos al eliminar usuario', [
                'usuario_id' => $usuario->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql' => $e->getSql() ?? 'N/A'
            ]);

            $errorMessage = 'No se pudo eliminar el usuario debido a restricciones de base de datos.';
            if (str_contains($e->getMessage(), 'foreign key')) {
                $errorMessage = 'No se puede eliminar el usuario porque tiene registros relacionados (ventas, pagos, etc.).';
            }

            return redirect()->route('usuarios.index')
                ->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Error general al eliminar usuario en controller', [
                'usuario_id' => $usuario->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('usuarios.index')
                ->with('error', 'No se pudo eliminar el usuario: ' . $e->getMessage());
        }
    }
}
