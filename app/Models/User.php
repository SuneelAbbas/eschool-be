<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
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

    public function canAccessInstitute(int $instituteId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return $this->institute_id === $instituteId;
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
}
