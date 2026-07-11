<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy(function($perm) {
            return explode('_', $perm->name, 2)[1] ?? 'other';
        });
        return view('admin.roles.form', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create(['name' => $validated['name']]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all()->groupBy(function($perm) {
            return explode('_', $perm->name, 2)[1] ?? 'other';
        });
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('admin.roles.form', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent editing super_admin role name if desired, or just validate
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
        ]);

        $role->update(['name' => $validated['name']]);
        
        $permissions = $request->permissions ?? [];
        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        
        if ($role->name === 'super_admin') {
            return redirect()->route('admin.roles.index')->with('error', 'The Super Admin role cannot be deleted.');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * Delete multiple roles at once (never the super_admin role).
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:roles,id',
        ]);

        $roles = Role::whereIn('id', $validated['ids'])->where('name', '!=', 'super_admin')->get();
        foreach ($roles as $role) {
            $role->delete();
        }

        return redirect()->route('admin.roles.index')
            ->with('success', $roles->count() . ' role(s) deleted successfully.');
    }
}
