<?php

namespace Database\Seeders;

use App\Models\ApprovalMatrix;
use Illuminate\Database\Seeder;

/*

php artisan db:seed --class=ApprovalMatrixSeeder

*/
class ApprovalMatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        ApprovalMatrix::truncate();

        // Use ID 1 for all seeded entries
        $creatorId = 1;

        // Matrix 1: Sampai dengan Rp.500.000.000 (lima ratus juta rupiah)
        ApprovalMatrix::create([
            'name' => 'Justifikasi Kebutuhan HO Level 1',
            'min_budget' => 0,
            'max_budget' => 500000000, // 500 juta
            'approvers' => [
                'Unit Pemilik Program',
                'Mgr. Pemilik Program',
                'Mgr. Business Feasibility',
                'Mgr. Management Accounting',
                'Mgr. Governance & Process Evaluation',
                'VP/OVP. Pemilik Program'
            ],
            'description' => 'Kewenangan justifikasi kebutuhan untuk anggaran sampai dengan Rp.500.000.000',
            'status' => 'Active',
            'created_by' => $creatorId,
        ]);

        // Matrix 2: Sampai dengan Rp.3.000.000.000 (tiga miliar rupiah)
        ApprovalMatrix::create([
            'name' => 'Justifikasi Kebutuhan HO Level 2',
            'min_budget' => 500000000, // 500 juta
            'max_budget' => 3000000000, // 3 miliar
            'approvers' => [
                'Mgr. Pemilik Program',
                'VP. Corp. Strategy',
                'VP. Finance Plan & Reporting',
                'VP. Risk Management'
            ],
            'description' => 'Kewenangan justifikasi kebutuhan untuk anggaran sampai dengan Rp.3.000.000.000',
            'status' => 'Active',
            'created_by' => $creatorId,
        ]);

        // Matrix 3: Sampai dengan Rp.25.000.000.000 (dua puluh lima miliar rupiah)
        ApprovalMatrix::create([
            'name' => 'Justifikasi Kebutuhan HO Level 3',
            'min_budget' => 3000000000, // 3 miliar
            'max_budget' => 25000000000, // 25 miliar
            'approvers' => [
                'VP/OVP Pemilik Program',
                'VP. Corp. Strategy',
                'VP. Finance Plan & Reporting',
                'VP. Risk Management',
                'Direktur Pemilik Program',
                'Direktur Finance'
            ],
            'description' => 'Kewenangan justifikasi kebutuhan untuk anggaran sampai dengan Rp.25.000.000.000',
            'status' => 'Active',
            'created_by' => $creatorId,
        ]);

        // Matrix 4: Lebih dari Rp.25.000.000.000 (dua puluh lima miliar rupiah)
        ApprovalMatrix::create([
            'name' => 'Justifikasi Kebutuhan HO Level 4',
            'min_budget' => 25000000000, // 25 miliar
            'max_budget' => null, // Unlimited
            'approvers' => [
                'VP/OVP Pemilik Program',
                'VP. Corp. Strategy',
                'VP. Finance Plan & Reporting',
                'VP. Risk Management',
                'Direktur Pemilik Program',
                'Direktur Finance',
                'Direktur Utama'
            ],
            'description' => 'Kewenangan justifikasi kebutuhan untuk anggaran lebih dari Rp.25.000.000.000',
            'status' => 'Active',
            'created_by' => $creatorId,
        ]);

        $this->command->info('Approval Matrix seeded successfully!');
    }
}
