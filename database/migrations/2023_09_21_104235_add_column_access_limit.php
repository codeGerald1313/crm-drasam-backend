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
        Schema::table('users', function (Blueprint $table) {
            $table->time('hour_start')->nullable();
            $table->time('hour_end')->nullable();
            $table->boolean('inline')->default(0);  
            $table->boolean('admin')->default(0);     
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('hour_start');
            $table->dropColumn('hour_end');
            $table->dropColumn('inline');
            $table->dropColumn('admin');
        });
    }
};
