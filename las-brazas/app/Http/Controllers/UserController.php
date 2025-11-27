<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('roles:id,nombre')->orderBy('id', 'desc')->paginate(10);
        $roles = Role::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'cedula' => 'nullable|string|max:50',
            'celular' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        // Campos opcionales si existen en la tabla
        foreach (['cedula', 'celular', 'direccion'] as $f) {
            if (array_key_exists($f, $data)) {
                $user->{$f} = $data[$f];
            }
        }
        $user->save();

        if (!empty($data['role_id'])) {
            $user->roles()->sync([$data['role_id']]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'cedula' => 'nullable|string|max:50',
            'celular' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        foreach (['cedula', 'celular', 'direccion'] as $f) {
            if (array_key_exists($f, $data)) {
                $user->{$f} = $data[$f];
            }
        }
        $user->save();

        if (array_key_exists('role_id', $data)) {
            $user->roles()->sync($data['role_id'] ? [$data['role_id']] : []);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->roles()->detach();
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado');
    }
}


