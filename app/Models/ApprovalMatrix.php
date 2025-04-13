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
}
