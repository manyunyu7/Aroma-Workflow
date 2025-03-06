<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisAnggaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nama', 'is_show', 'created_by', 'updated_by'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id;
        });
    }

    /**
     * Relasi ke Workflow (One-to-Many).
     */
    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class, 'jenis_anggaran');
    }
}
