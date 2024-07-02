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
        Schema::create('diffusions', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_name');
            $table->string('content_type');
            $table->string('content_reference');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('date')->default(now()); // Agregar un valor predeterminado
            $table->boolean('status')->default(1); 
            $table->timestamps();  
            
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diffusions');
    }
};
