<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nik',
        'object_id',
        'unit_kerja',
        'jabatan',
        'status',
        'created_by',
        'edited_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the roles associated with the user.
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Get the roles for the user.
     */
    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        return $this->userRoles()->where('role', $role)->exists();
    }

    /**
     * Get user roles with budget limits
     */
    public function getRolesWithBudget()
    {
        return $this->userRoles()->get(['role', 'max_budget']);
    }

    /**
     * Get roles that can handle a specific budget amount
     */
    public function getRolesForBudget($amount)
    {
        return $this->userRoles()
            ->where('max_budget', '>=', $amount)
            ->get(['role']);
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include users from a specific unit.
     */
    public function scopeFromUnit($query, $unitKerja)
    {
        return $query->where('unit_kerja', $unitKerja);
    }

    /**
     * Scope a query to only include users with a specific role and budget capability.
     */
    public function scopeWithRoleAndBudget($query, $role, $budget = 0)
    {
        return $query->whereHas('userRoles', function ($q) use ($role, $budget) {
            $q->where('role', $role);
            if ($budget > 0) {
                $q->where('max_budget', '>=', $budget);
            }
        });
    }
}
