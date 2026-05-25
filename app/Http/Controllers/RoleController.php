<?php

namespace App\Http\Controllers;

use App\Enums\TeamPermission;
use App\Enums\TeamRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of roles and permissions.
     */
    public function index(): Response
    {
        $roles = Role::orderBy('level', 'desc')->get()->map(function (Role $role) {
            return [
                'id' => $role->id,
                'slug' => $role->slug,
                'name' => $role->name,
                'description' => $role->description,
                'isSystem' => $role->is_system,
                'level' => $role->level,
                'permissions' => $role->permissions()->pluck('slug'),
            ];
        });

        $permissions = Permission::orderBy('group')->orderBy('name')->get()->map(function (Permission $perm) {
            return [
                'id' => $perm->id,
                'slug' => $perm->slug,
                'name' => $perm->name,
                'group' => $perm->group,
            ];
        });

        $permissionGroups = $permissions->groupBy('group')->map(fn ($perms, $group) => [
            'name' => $group,
            'permissions' => $perms->values(),
        ])->values();

        return Inertia::render('admin/Roles', [
            'roles' => $roles,
            'permissions' => $permissions,
            'permissionGroups' => $permissionGroups,
            'availableRoles' => array_map(fn ($role) => [
                'value' => $role->value,
                'label' => $role->label(),
                'permissions' => array_map(fn ($p) => $p->value, $role->permissions()),
            ], TeamRole::cases()),
        ]);
    }

    /**
     * Update role permissions.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403, 'No se pueden modificar roles del sistema.');

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        $permissionIds = Permission::whereIn('slug', $validated['permissions'])->pluck('id');

        $role->permissions()->sync($permissionIds);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Permisos actualizados correctamente.']);

        return back();
    }

    /**
     * Store a newly created role.
     *
     * NOTA: los roles personalizados quedan en el catálogo (tabla `roles`)
     * pero AÚN NO son asignables a miembros, porque los modelos Membership y
     * TeamInvitation castean `role` al enum TeamRole. Para hacerlos asignables
     * habría que migrar esos casts a string. Por ahora la utilidad real del
     * módulo es EDITAR los permisos de los roles existentes.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'description' => 'nullable|string|max:255',
            'level' => 'nullable|integer|min:1|max:99',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        // Slug único garantizado (evita choque con roles existentes).
        $base = \Illuminate\Support\Str::slug($validated['name']);
        $slug = $base;
        $i = 1;
        while (Role::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        $role = Role::create([
            'slug' => $slug,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_system' => false,
            'level' => $validated['level'] ?? 1,
        ]);

        $permissionIds = Permission::whereIn('slug', $validated['permissions'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rol creado correctamente.']);

        return back();
    }

    /**
     * Delete a custom (non-system) role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403, 'No se pueden eliminar roles del sistema.');

        $role->permissions()->detach();
        $role->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rol eliminado correctamente.']);

        return back();
    }
}