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
        if (!Schema::hasColumn('quickly_answers', 'category')) {
            Schema::table('quickly_answers', function (Blueprint $table) {
                $table->string('category')->nullable();
            });
        }

        if (!Schema::hasColumn('quickly_answers', 'prefix_lang')) {
            Schema::table('quickly_answers', function (Blueprint $table) {
                $table->string('prefix_lang')->nullable();
            });
        }

        if (!Schema::hasColumn('quickly_answers', 'type_media')) {
            Schema::table('quickly_answers', function (Blueprint $table) {
                $table->string('type_media')->nullable();
            });
        }

        if (!Schema::hasColumn('quickly_answers', 'link')) {
            Schema::table('quickly_answers', function (Blueprint $table) {
                $table->text('link')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('quickly_answers', 'category')) {
            Schema::table('quickly_answers', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }

        if (Schema::hasColumn('quickly_answers', 'prefix_lang')) {
            Schema::table('quickly_answers', function (Blueprint $table) {
                $table->dropColumn('prefix_lang');
            });
        }
        
        if (Schema::hasColumn('quickly_answers', 'type_media')) {
            Schema::table('quickly_answers', function (Blueprint $table) {
                $table->dropColumn('type_media');
            });
        }

        if (Schema::hasColumn('quickly_answers', 'link')) {
            Schema::table('quickly_answers', function (Blueprint $table) {
                $table->dropColumn('link');
            });
        }
    }
};
