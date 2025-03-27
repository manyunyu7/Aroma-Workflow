<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'user_id',
        'role', // Dibuat Oleh, Diperiksa Oleh, Disetujui Oleh
        'status', // Pending, Approved, Rejected
        'notes',
        'attachment',
        'digital_signature',
        'sequence', // Urutan approval
        'is_active', // Level approval
        'approved_at',
        'rejected_at',
        'updated_by',
    ];

    /**
     * Get the workflow that owns this approval.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the user who last updated this approval.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
