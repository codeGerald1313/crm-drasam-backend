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
        Schema::create('conexion', function (Blueprint $table) {
            $table->id();

            $table->text('token')->nullable();
            $table->string('company_name');
            $table->string('phone');
            $table->string('phone_id')->nullable();
            $table->text('welcome')->nullable();
            
            $table->integer('status');
            $table->integer('status_bot')->default(1);
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conexion');
    }
};
