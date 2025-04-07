<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_pengajuan',
        'unit_kerja',
        'nama_kegiatan',
        'jenis_anggaran',
        'total_nilai',
        'deskripsi_kegiatan',
        'cost_center',
        'waktu_penggunaan',
        'account',
        'justification_form',
        'status',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'total_nilai' => 'decimal:2',
        'waktu_penggunaan' => 'date',
    ];

    /**
     * Relation to JenisAnggaran (Many-to-One).
     */
    public function jenisAnggaran(): BelongsTo
    {
        return $this->belongsTo(JenisAnggaran::class, 'jenis_anggaran');
    }

    /**
     * Get the approvals associated with the workflow.
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(WorkflowApproval::class);
    }

    /**
     * Get the documents associated with the workflow.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(WorkflowDocument::class);
    }

    /**
     * Get the creator of this workflow.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the current active approvers.
     */
    public function activeApprovers()
    {
        return $this->approvals()
            ->where('is_active', 1)
            ->where('status', 'PENDING')
            ->get();
    }

    /**
     * Get available workflow statuses with their codes and names
     * This method retrieves unique roles from the UserRole model
     */
    public static function getStatuses()
    {
        // Try to get unique roles from the UserRole model
        try {
            $roles = UserRole::select('role')
                ->distinct()
                ->whereNotNull('role')
                ->where('role', '!=', '')
                ->get();

            // If roles found in database, map them to the required format
            if ($roles->count() > 0) {
                return $roles->map(function ($roleObj) {
                    $roleName = $roleObj->role;
                    // Generate a code version of the name (for internal use)
                    $code = strtoupper(str_replace([' ', '-'], '_', $roleName));

                    return [
                        'code' => $code,
                        'name' => $roleName
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching roles from UserRole model: ' . $e->getMessage());
            // If any error occurs, fall back to default roles
        }

        // Default roles (fallback)
        return [
            [
                'code' => 'CREATOR',
                'name' => 'Creator'
            ],
            [
                'code' => 'ACKNOWLEDGER',
                'name' => 'Acknowledger'
            ],
            [
                'code' => 'UNIT_HEAD_APPROVER',
                'name' => 'Unit Head - Approver'
            ],
            [
                'code' => 'REVIEWER_MAKER',
                'name' => 'Reviewer-Maker'
            ],
            [
                'code' => 'REVIEWER_APPROVER',
                'name' => 'Reviewer-Approver'
            ]
        ];
    }

    /**
     * Get status name from status code
     */
    public static function getStatusName($code)
    {
        // For codes stored in the database that match the display names directly
        if (in_array($code, ['Creator', 'Acknowledger', 'Unit Head - Approver', 'Reviewer-Maker', 'Reviewer-Approver'])) {
            return $code;
        }

        // For old-style codes, provide mapping
        $roleNames = [
            'CREATOR' => 'Creator',
            'ACKNOWLEDGED_BY_SPV' => 'Acknowledger',
            'APPROVED_BY_HEAD_UNIT' => 'Unit Head - Approver',
            'REVIEWED_BY_MAKER' => 'Reviewer-Maker',
            'REVIEWED_BY_APPROVER' => 'Reviewer-Approver'
        ];

        // Return mapped name if available, otherwise return the code itself
        return $roleNames[$code] ?? $code;
    }
    /**
     * Get formatted status for display.
     */
    public function getFormattedStatusAttribute()
    {
        $statusMap = [
            'DRAFT_CREATOR' => 'Draft (Creator)',
            'DRAFT_REVIEWER' => 'Draft (Reviewer)',
            'WAITING_APPROVAL' => 'Waiting Approval',
            'DIGITAL_SIGNING' => 'Digital Signing',
            'COMPLETED' => 'Completed',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    /**
     * Get progress percentage of the workflow.
     */
    public function getProgressPercentageAttribute()
    {
        $totalApprovals = $this->approvals->count();
        if ($totalApprovals === 0) return 0;

        $completedApprovals = $this->approvals->whereIn('status', ['APPROVED', 'REJECTED'])->count();

        return round(($completedApprovals / $totalApprovals) * 100);
    }

    /**
     * Get status color for UI display.
     */
    public function getStatusColorAttribute()
    {
        $colorMap = [
            'DRAFT_CREATOR' => 'secondary',
            'DRAFT_REVIEWER' => 'info',
            'WAITING_APPROVAL' => 'warning',
            'DIGITAL_SIGNING' => 'primary',
            'COMPLETED' => 'success',
        ];

        return $colorMap[$this->status] ?? 'secondary';
    }
}
