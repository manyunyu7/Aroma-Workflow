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
     * Get the list of statuses with code and name.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            ['code' => 'CREATOR', 'name' => 'Created By'],
            ['code' => 'ACKNOWLEDGED_BY_SPV', 'name' => 'Acknowledge by Spv (if any)'],
            ['code' => 'APPROVED_BY_HEAD_UNIT', 'name' => 'Approval by Head Unit'],
            ['code' => 'REVIEWED_BY_MAKER', 'name' => 'Reviewer-Maker'],
            ['code' => 'REVIEWED_BY_APPROVER', 'name' => 'Reviewer-Approver'],
        ];
    }

    /**
     * Get the name of a status by its code.
     *
     * @param string $code
     * @return string|null
     */
    public static function getStatusName(string $code): ?string
    {
        $statuses = self::getStatuses();
        $status = collect($statuses)->firstWhere('code', $code);
        return $status['name'] ?? null;
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
