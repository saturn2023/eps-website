<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Inner pages (About, Parent Hub, etc). Each page has a fixed header
     * (title, icon, intro) and a stack of content blocks — like ACF Flexible Content.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('section')->index();
            $table->unsignedInteger('sort')->default(0);
            $table->string('heading')->nullable();
            $table->text('intro')->nullable();
            $table->string('icon')->nullable();
            $table->json('blocks')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
