<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->text('user_id');
            $table->string('role'); // Dibuat Oleh, Diperiksa Oleh, Disetujui Oleh
            $table->text('notes')->nullable();
            $table->text('attachment')->nullable(); // File upload
            $table->unsignedInteger('sequence')->default(1); // Order of approval
            $table->integer('is_active')->default(0); // Level of approval
            $table->integer('digital_signature')->default(0)->nullable(); // Level of approval
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'REVISED', 'CANCELLED','DRAFT'])->default('PENDING');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_approvals');
    }
};
