<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'user_type',
        'institute_id',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isSuperAdmin(): bool
    {
        return $this->user_type === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->user_type === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->user_type === 'student';
    }

    public function isParent(): bool
    {
        return $this->user_type === 'parent';
    }

    public function isAccountant(): bool
    {
        return $this->user_type === 'accountant';
    }

    public function isLibrarian(): bool
    {
        return $this->user_type === 'librarian';
    }

    public function hasRole(array|string $roles): bool
    {
        $roles = is_string($roles) ? [$roles] : $roles;
        return in_array($this->user_type, $roles);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->directPermissions()->where('slug', $permission)->exists()) {
            return true;
        }

        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('slug', $permission);
        })->exists();
    }

    public function canAccessInstitute(int $instituteId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return $this->institute_id === $instituteId;
    }

    public function assignRole(Role $role, ?int $assignedBy = null): void
    {
        if (!$this->hasRoleId($role->id)) {
            $this->roles()->attach($role->id, ['assigned_by' => $assignedBy]);
        }
    }

    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
    }

    public function syncRoles(array $roleIds, ?int $assignedBy = null): void
    {
        $syncData = [];
        foreach ($roleIds as $roleId) {
            $syncData[$roleId] = ['assigned_by' => $assignedBy];
        }
        $this->roles()->sync($syncData);
    }

    public function hasRoleId(int $roleId): bool
    {
        return $this->roles()->where('roles.id', $roleId)->exists();
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->permissions();
    }

    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        $permissions = collect();

        $directPermissions = $this->directPermissions()->get();
        $permissions = $permissions->merge($directPermissions);

        $rolePermissions = $this->roles()->with('permissions')->get()
            ->pluck('permissions')
            ->flatten();
        $permissions = $permissions->merge($rolePermissions);

        return $permissions->unique('id');
    }

    public static function validUserTypes(): array
    {
        return [
            'super_admin',
            'admin',
            'teacher',
            'student',
            'parent',
            'accountant',
            'librarian',
        ];
    }

    public static function validStatuses(): array
    {
        return [
            'active',
            'pending',
            'suspended',
            'inactive',
        ];
    }
}
