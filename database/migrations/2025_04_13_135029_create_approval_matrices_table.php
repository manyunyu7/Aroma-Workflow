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
        // Create a migration for approval_matrices table
        Schema::create('approval_matrices', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // Name of the matrix (e.g., "Head Office Matrix")
            $table->decimal('min_budget', 20, 2);    // Minimum budget amount
            $table->decimal('max_budget', 20, 2)->nullable(); // Maximum budget amount (null for unlimited)
            $table->text('approvers')->nullable();   // JSON array of approver roles
            $table->text('description')->nullable(); // Optional description
            $table->string('status')->default('Active'); // Active/Not Active
            $table->string('created_by');
            $table->string('edited_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_matrices');
    }
};
