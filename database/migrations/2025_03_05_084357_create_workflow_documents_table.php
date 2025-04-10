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
        Schema::create('workflow_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->foreignId('uploaded_by')->constrained('users');

            $table->enum('document_category', ['MAIN', 'SUPPORTING'])->default('SUPPORTING');
            $table->enum('document_type', ['JUSTIFICATION_DOC', 'REVIEW_DOC', 'OTHER'])->default('OTHER');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_documents');
    }
};
