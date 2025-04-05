<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'name',
        'unit_kerja',
        'jabatan',
        'status',
        'created_by',
        'edited_by'
    ];

    /**
     * Get the roles for the user.
     */
    public function roles()
    {
        return $this->hasMany(MasterUserRole::class, 'master_user_id');
    }
}
