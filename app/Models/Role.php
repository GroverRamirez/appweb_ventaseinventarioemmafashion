<?php

namespace App\Models;

use App\Enums\TeamPermission as TeamPermissionEnum;
use App\Enums\TeamRole as TeamRoleEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['slug', 'name', 'description', 'is_system', 'level'])]
class Role extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'level' => 'integer',
        ];
    }

    /**
     * Get the permissions belonging to this role.
     *
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * Determine if this role has the given permission (slug or enum).
     */
    public function hasPermission(TeamPermissionEnum|string $permission): bool
    {
        $slug = $permission instanceof TeamPermissionEnum
            ? $permission->value
            : $permission;

        return $this->permissions()->where('slug', $slug)->exists();
    }

    /**
     * Resolve the corresponding TeamRole enum case, if any.
     */
    public function toEnum(): ?TeamRoleEnum
    {
        return TeamRoleEnum::tryFrom($this->slug);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
