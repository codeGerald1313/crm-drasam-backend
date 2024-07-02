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
        Schema::create('conversation_reopenings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->dateTime('reopened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('conversations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_reopenings');
    }
};
