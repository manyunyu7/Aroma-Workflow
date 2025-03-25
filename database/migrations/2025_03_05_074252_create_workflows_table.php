<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            $table->text('unit_kerja');
            $table->text('nama_kegiatan');
            $table->text('jenis_anggaran');
            $table->decimal('total_nilai', 15, 2);
            $table->date('waktu_penggunaan');
            $table->text('account')->nullable();
            $table->text('justification_form')->nullable();
            $table->text('cost_center')->nullable();

            // Add status column with UPPERCASE values
            $table->enum('status', [
                'DRAFT_CREATOR',
                'DRAFT_REVIEWER',
                'WAITING_APPROVAL',
                'DIGITAL_SIGNING',
                'COMPLETED'
            ])->default('DRAFT_CREATOR');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
