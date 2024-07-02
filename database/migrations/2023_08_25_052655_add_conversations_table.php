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
        Schema::create('conversations', function (Blueprint $table){
            $table->id();
            $table->string('uuid')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->datetime('start_date')->nullable();
            $table->datetime('last_activity')->nullable();
            $table->string('status')->nullable();
            $table->integer('contador')->nullable();
            $table->integer('status_bot');
            $table->string('channel_id')->nullable();
            $table->timestamps();
            
            $table->foreign('contact_id')->references('id')->on('contacts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
