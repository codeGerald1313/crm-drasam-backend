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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->string('api_id')->nullable()->unique();
            $table->json('content')->nullable();
            $table->string('type')->nullable();
            $table->datetime('date_of_issue')->nullable();;
            $table->string('status')->nullable();
            $table->enum('emisor', ['Advisor', 'Customer']);
            $table->integer('emisor_id')->nullable();
            $table->timestamps();
            
            $table->foreign('conversation_id')->references('id')->on('conversations');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index('api_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};