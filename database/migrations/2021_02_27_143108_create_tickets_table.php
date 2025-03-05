<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger("sender_id");
            $table->string("ticket_title")->nullable();
            $table->string("ticket_detail")->nullable();
            $table->string("ticket_photo")->nullable();
            $table->string("status")->nullable();
            $table->string("durasi")->nullable();
            $table->string("priority")->nullable();
            $table->foreign("sender_id")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
