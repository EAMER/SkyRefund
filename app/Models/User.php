<?php

namespace App\Models;

use App\Enums\Department;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
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
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',

            'department' => Department::class,
            'role' => UserRole::class,

            'active' => 'boolean',
        ];
    }

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
     * Refunds currently assigned to this user.
     */
    public function assignedRefunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'assigned_to');
    }
}