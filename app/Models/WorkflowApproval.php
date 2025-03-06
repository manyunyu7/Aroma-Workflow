<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'user_id',
        'role', // Dibuat Oleh, Diperiksa Oleh, Disetujui Oleh
        'status', // Pending, Approved, Rejected
        'comments',
        'attachment',
    ];

    /**
     * Get the workflow that owns this approval.
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the user assigned to this approval.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
