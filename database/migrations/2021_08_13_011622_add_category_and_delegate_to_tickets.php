<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryAndDelegateToTickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('category')->nullable();
            $table->unsignedBigInteger('delegate_id')->nullable();
            $table->foreign('delegate_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category')->references('id')->on('ticket_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['delegate_id']);
            $table->dropForeign(['category']);

            // Drop the columns
            $table->dropColumn('delegate_id');
            $table->dropColumn('category');
        });
    }
}
