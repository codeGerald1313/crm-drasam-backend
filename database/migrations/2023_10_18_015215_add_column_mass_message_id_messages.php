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
        if (!Schema::hasColumn('messages', 'mass_message_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->unsignedBigInteger('mass_message_id')->nullable();
                $table->foreign('mass_message_id')->references('id')->on('diffusions');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('messages', 'mass_message_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign(['mass_message_id']);
                $table->dropColumn('mass_message_id');
            });
        }
    }
};
