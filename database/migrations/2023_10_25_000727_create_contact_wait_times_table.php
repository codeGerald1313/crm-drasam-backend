<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactWaitTimesTable extends Migration
{
    public function up()
    {
        Schema::create('contact_wait_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->integer('wait_time');
            $table->timestamps();
            $table->foreign('contact_id')->references('id')->on('contacts');
            $table->unsignedBigInteger('advisor_id')->nullable();
            $table->foreign('advisor_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contact_wait_times');
    }
}
