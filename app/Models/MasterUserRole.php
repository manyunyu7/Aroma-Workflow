<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterUserRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_user_id',
        'role'
    ];

    /**
     * Get the user that owns the role.
     */
    public function masterUser()
    {
        return $this->belongsTo(MasterUser::class, 'master_user_id');
    }
}
