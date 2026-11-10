<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Editable content for fixed pages (home page, site settings).
     * One row per page, all fields stored as JSON — similar to ACF field groups.
     */
    public function up(): void
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('content')->nullable();
            $table->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email');
            $table->string('topic')->nullable();
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('page_contents');
    }
};
