<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'user_id',
        'action',
        'status_before',
        'status_after',
        'role',
        'notes',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the workflow that this log belongs to
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Predefined log actions for consistent usage
     */
    public static function getActions()
    {
        return [
            'CREATE' => 'Create workflow',
            'UPDATE' => 'Update workflow',
            'SUBMIT' => 'Submit workflow',
            'APPROVE' => 'Approve workflow',
            'REJECT' => 'Reject workflow',
            'CANCEL' => 'Cancel workflow',
            'ADD_DOCUMENT' => 'Add document',
            'REMOVE_DOCUMENT' => 'Remove document',
            'SAVE_DRAFT' => 'Save draft',
            'ASSIGN_APPROVER' => 'Assign approver',
            'REMOVE_APPROVER' => 'Remove approver',
            'VIEW' => 'View workflow',
            'SYSTEM_UPDATE' => 'System update',
        ];
    }
}
