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
        if (!Schema::hasColumn('messages', 'referral')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->longtext('referral')->nullable();
            });
        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('messages', 'referral')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('referral');
            });
        }
    }
};
