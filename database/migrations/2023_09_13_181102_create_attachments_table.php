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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachment_id')->nullable();
            $table->string('message_id');
            $table->text('url')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('sha256')->nullable();
            $table->string('file_size')->nullable();
            $table->string('messaging_product');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
