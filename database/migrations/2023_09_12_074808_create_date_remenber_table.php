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
        Schema::create('date_remenber', function (Blueprint $table) {
            $table->id();
            $table->date('date_to_remenber')->nullable();
            $table->time('time_to_remenber')->nullable();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->integer('status');
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('conversations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('date_remenber');
    }
};
