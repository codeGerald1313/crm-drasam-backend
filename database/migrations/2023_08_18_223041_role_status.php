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
        Schema::create('role_status', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->default(1);
            $table->unsignedBigInteger('id_role');
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('id_role')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_status');
    }
};
