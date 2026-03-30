<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        // Usamos una consulta manual para el conteo de usuarios por rol para evitar el error de relación circular en morphedByMany
        $roles = Role::with('permissions')->orderBy('name')->get();
        
        foreach ($roles as $role) {
            $role->users_count = \DB::table(config('permission.table_names.model_has_roles'))
                ->where('role_id', $role->id)
                ->count();
        }

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', "Rol «{$role->name}» creado correctamente.");
    }

    public function edit(Role $role)
    {
        $permissions      = Permission::orderBy('name')->get();
        $rolePermissions  = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', "unique:roles,name,{$role->id}"],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')
            ->with('success', "Rol «{$role->name}» actualizado correctamente.");
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['super-admin'])) {
            return redirect()->route('roles.index')
                ->with('error', 'No puedes eliminar el rol super-admin.');
        }

        $name = $role->name;
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Rol «{$name}» eliminado correctamente.");
    }
}
