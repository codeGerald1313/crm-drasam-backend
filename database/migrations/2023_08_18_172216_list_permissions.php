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
        Schema::create('permissions_list', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_module');
            $table->unsignedBigInteger('id_permission');
            
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id_module')->references('id')->on('module_permission');
            $table->foreign('id_permission')->references('id')->on('permissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions_list');
    }
};
