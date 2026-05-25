<?php

use App\Enums\TeamPermission;
use App\Enums\TeamRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Pobla las tablas `roles`, `permissions` y `role_permissions` con los
     * datos definidos en los enums TeamRole y TeamPermission. Los enums
     * siguen siendo la fuente de verdad del código; la BD es ahora reflejo
     * consultable.
     */
    public function up(): void
    {
        $now = now();

        // ---------- Permisos ----------
        // Catálogo legible — etiquetas en español y agrupados por módulo.
        $permissionCatalog = [
            // Gestión de equipo (solo Propietaria)
            TeamPermission::UpdateTeam->value => ['name' => 'Actualizar equipo', 'group' => 'team'],
            TeamPermission::DeleteTeam->value => ['name' => 'Eliminar equipo', 'group' => 'team'],
            TeamPermission::AddMember->value => ['name' => 'Agregar miembros', 'group' => 'member'],
            TeamPermission::UpdateMember->value => ['name' => 'Actualizar miembros', 'group' => 'member'],
            TeamPermission::RemoveMember->value => ['name' => 'Quitar miembros', 'group' => 'member'],
            TeamPermission::CreateInvitation->value => ['name' => 'Crear invitaciones', 'group' => 'invitation'],
            TeamPermission::CancelInvitation->value => ['name' => 'Cancelar invitaciones', 'group' => 'invitation'],
            // Módulos de negocio
            TeamPermission::ViewDashboard->value => ['name' => 'Ver dashboard', 'group' => 'dashboard'],
            TeamPermission::ViewProduct->value => ['name' => 'Ver productos', 'group' => 'product'],
            TeamPermission::ManageProduct->value => ['name' => 'Gestionar productos', 'group' => 'product'],
            TeamPermission::ViewCategory->value => ['name' => 'Ver categorías', 'group' => 'category'],
            TeamPermission::ManageCategory->value => ['name' => 'Gestionar categorías', 'group' => 'category'],
            TeamPermission::ViewSupplier->value => ['name' => 'Ver proveedores', 'group' => 'supplier'],
            TeamPermission::ManageSupplier->value => ['name' => 'Gestionar proveedores', 'group' => 'supplier'],
            TeamPermission::ViewPurchase->value => ['name' => 'Ver compras', 'group' => 'purchase'],
            TeamPermission::CreatePurchase->value => ['name' => 'Registrar compras', 'group' => 'purchase'],
            TeamPermission::ViewSale->value => ['name' => 'Ver ventas', 'group' => 'sale'],
            TeamPermission::CreateSale->value => ['name' => 'Registrar ventas', 'group' => 'sale'],
            TeamPermission::ViewReport->value => ['name' => 'Ver reportes', 'group' => 'report'],
        ];

        $permissionRows = [];
        foreach ($permissionCatalog as $slug => $meta) {
            $permissionRows[] = [
                'slug' => $slug,
                'name' => $meta['name'],
                'group' => $meta['group'],
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('permissions')->upsert($permissionRows, ['slug'], ['name', 'group', 'updated_at']);

        // ---------- Roles ----------
        $roleRows = [];
        foreach (TeamRole::cases() as $role) {
            $roleRows[] = [
                'slug' => $role->value,
                'name' => $role->label(),
                'description' => $role === TeamRole::Owner
                    ? 'Acceso total al sistema y gestión de usuarios.'
                    : 'Acceso parcial: ventas, compras y proveedores.',
                'is_system' => true,
                'level' => $role->level(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('roles')->upsert($roleRows, ['slug'], ['name', 'description', 'level', 'updated_at']);

        // ---------- Pivote role_permissions ----------
        $roleIds = DB::table('roles')->pluck('id', 'slug');
        $permissionIds = DB::table('permissions')->pluck('id', 'slug');

        $pivotRows = [];
        foreach (TeamRole::cases() as $role) {
            $roleId = $roleIds[$role->value] ?? null;
            if (! $roleId) {
                continue;
            }

            foreach ($role->permissions() as $permission) {
                $permissionId = $permissionIds[$permission->value] ?? null;
                if (! $permissionId) {
                    continue;
                }

                $pivotRows[] = [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($pivotRows)) {
            // Limpiamos y reinsertamos para que coincida con el estado del enum
            DB::table('role_permissions')->whereIn('role_id', $roleIds->values())->delete();
            DB::table('role_permissions')->insert($pivotRows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
    }
};
