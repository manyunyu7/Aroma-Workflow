<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'file_path',
        'file_name',
        'file_type',
        'uploaded_by',
        'document_category',  // New column
        'document_type',      // New column
        'notes',
    ];

    /**
     * Get the workflow that owns this document.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the user who uploaded this document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
