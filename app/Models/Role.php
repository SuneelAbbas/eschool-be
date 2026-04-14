<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    public function isSystemRole(): bool
    {
        return $this->is_system === true;
    }

    public function isProtected(): bool
    {
        return in_array($this->slug, ['super_admin', 'admin']);
    }

    public static function getSystemSlugs(): array
    {
        return ['super_admin'];
    }

    public static function getDashboardRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereNotIn('slug', static::getSystemSlugs())
            ->orderBy('sort_order')
            ->get();
    }
}
