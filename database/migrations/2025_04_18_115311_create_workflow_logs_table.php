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
        Schema::create('workflow_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Nullable for system actions
            $table->string('action', 50); // CREATE, UPDATE, APPROVE, REJECT, etc.
            $table->string('status_before', 50)->nullable();
            $table->string('status_after', 50)->nullable();
            $table->string('role', 50)->nullable(); // Role of the user at the time of action
            $table->text('notes')->nullable(); // Any additional notes about the action
            $table->json('metadata')->nullable(); // Any other data in JSON format
            $table->ipAddress('ip_address')->nullable(); // IP address of the user
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('workflow_id')
                ->references('id')
                ->on('workflows')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes for better performance
            $table->index('workflow_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_logs');
    }
};
