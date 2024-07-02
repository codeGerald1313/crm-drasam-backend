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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('advisor_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->unsignedBigInteger('reason_id')->nullable();
            $table->boolean('state')->nullable();
            $table->timestamp('time')->nullable();
            $table->text('interes_en')->nullable();
            $table->rememberToken();
            
            $table->timestamps();
            
            $table->foreign('advisor_id')->references('id')->on('users');
            $table->foreign('contact_id')->references('id')->on('contacts');
            $table->foreign('conversation_id')->references('id')->on('conversations');
            $table->foreign('reason_id')->references('id')->on('clousure_reasons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};