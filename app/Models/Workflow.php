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
        'status', // Ditambahkan agar bisa diisi massal
        'created_by', // Ditambahkan agar bisa diisi massal
    ];

    /**
     * Relasi ke JenisAnggaran (Many-to-One).
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
}
