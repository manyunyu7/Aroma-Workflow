<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'min_budget',
        'max_budget',
        'approval_matrix_id',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the user that owns the role.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }



    // In UserRole model
    public function approvalMatrix()
    {
        return $this->belongsTo(ApprovalMatrix::class);
    }

    /**
     * Get all roles that can handle the specified budget
     */
    public static function getRolesForBudget($budget)
    {
        return static::where('max_budget', '>=', $budget)
            ->distinct()
            ->pluck('role');
    }

    /**
     * Get all users with a specific role that can handle the budget
     */
    public static function getUsersWithRoleForBudget($role, $budget)
    {
        return static::where('role', $role)
            ->where('max_budget', '>=', $budget)
            ->with('user')
            ->get();
    }

    /**
     * Get predefined workflow roles
     */
    public static function getWorkflowRoles()
    {
        return [
            'Creator' => 'Creator',
            'Acknowledger' => 'Acknowledger',
            'Unit Head - Approver' => 'Unit Head - Approver',
            'Reviewer-Maker' => 'Reviewer-Maker',
            'Reviewer-Approver' => 'Reviewer-Approver'
        ];
    }
}
