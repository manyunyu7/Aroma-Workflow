<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// App\Models\ApprovalMatrix.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalMatrix extends Model
{
    protected $fillable = [
        'name',
        'min_budget',
        'max_budget',
        'approvers',
        'description',
        'status',
        'created_by',
        'edited_by'
    ];

    // Accessors & Mutators
    public function setApproversAttribute($value)
    {
        $this->attributes['approvers'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getApproversAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

       /**
     * Get the user who created this approval matrix
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this approval matrix
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Get the creator's name
     */
    public function getCreatorNameAttribute()
    {
        return $this->creator ? $this->creator->name : 'Unknown';
    }

    /**
     * Get the editor's name
     */
    public function getEditorNameAttribute()
    {
        return $this->editor ? $this->editor->name : 'Unknown';
    }
}
