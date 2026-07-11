<?php

namespace App\Models;

use App\Enums\Department;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'airline_id',
        'name',
        'email',
        'password',
        'department',
        'role',
        'active',
    ];

    /**
     * Hidden attributes.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',

        'department' => Department::class,
        'role' => UserRole::class,

        'active' => 'boolean',
    ];

    /**
     * Airline this user belongs to.
     */
    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    /**
     * Refunds created by this user.
     */
    public function createdRefunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'created_by');
    }

    /**
     * Refunds assigned to this user.
     */
    public function assignedRefunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'assigned_to');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(UserRole|string $role): bool
    {
        if ($role instanceof UserRole) {
            return $this->role === $role;
        }

        return $this->role->value === $role;
    }

    /**
     * Check if user belongs to a department.
     */
    public function inDepartment(Department|string $department): bool
    {
        if ($department instanceof Department) {
            return $this->department === $department;
        }

        return $this->department->value === $department;
    }

    /**
     * Check if user is a super administrator.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    /**
     * Check if account is active.
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}