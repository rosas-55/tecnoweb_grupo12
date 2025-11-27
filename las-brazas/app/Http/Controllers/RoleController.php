<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::orderBy('id','desc')->paginate(10);
        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);
        Role::create($data);
        return redirect()->route('roles.index')->with('success','Rol creado');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);
        $role->update($data);
        return redirect()->route('roles.index')->with('success','Rol actualizado');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success','Rol eliminado');
    }
}


