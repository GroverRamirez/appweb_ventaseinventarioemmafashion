<?php

namespace App\Enums;

enum TeamRole: string
{
    case Owner = 'owner';
    case Member = 'member';

    /**
     * Get the display label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Propietaria',
            self::Member => 'Vendedora',
        };
    }

    /**
     * Get all the permissions for this role.
     *
     * @return array<TeamPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            // Propietaria — acceso total a todo el sistema.
            self::Owner => TeamPermission::cases(),

            // Empleado — puede operar caja y abastecimiento, pero NO edita
            // el catálogo ni accede a reportes/gestión del equipo.
            self::Member => [
                TeamPermission::ViewDashboard,
                TeamPermission::ViewProduct,
                TeamPermission::ViewCategory,
                TeamPermission::ViewSupplier,
                TeamPermission::ManageSupplier,
                TeamPermission::ViewPurchase,
                TeamPermission::CreatePurchase,
                TeamPermission::ViewSale,
                TeamPermission::CreateSale,
            ],
        };
    }

    /**
     * Determine if the role has the given permission.
     */
    public function hasPermission(TeamPermission $permission): bool
    {
        return in_array($permission, $this->permissions());
    }

    /**
     * Get the hierarchy level for this role.
     * Higher numbers indicate higher privileges.
     */
    public function level(): int
    {
        return match ($this) {
            self::Owner => 2,
            self::Member => 1,
        };
    }

    /**
     * Check if this role is at least as privileged as another role.
     */
    public function isAtLeast(TeamRole $role): bool
    {
        return $this->level() >= $role->level();
    }

    /**
     * Get the roles that can be assigned to team members (excludes Owner).
     *
     * @return array<array{value: string, label: string}>
     */
    public static function assignable(): array
    {
        return collect(self::cases())
            ->filter(fn (self $role) => $role !== self::Owner)
            ->map(fn (self $role) => ['value' => $role->value, 'label' => $role->label()])
            ->values()
            ->toArray();
    }
}
