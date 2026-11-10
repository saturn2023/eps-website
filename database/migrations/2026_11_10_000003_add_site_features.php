<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Sub-group inside a section, e.g. Parent Hub > "Getting ready".
            $table->string('group')->nullable()->after('section');
            // The "Need help?" office card on the right of the content.
            $table->boolean('show_contact_card')->default(false)->after('icon');
        });

        // School calendar events.
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('time')->nullable();
            $table->string('type')->default('event'); // event | holiday | development_day
            $table->timestamps();
        });

        // Documents sent through the upload forms (enrolments, IEC).
        Schema::create('document_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('form');
            $table->string('parent_name');
            $table->string('email');
            $table->string('phone');
            $table->string('student_name');
            $table->string('year_level')->nullable();
            $table->json('files');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_submissions');
        Schema::dropIfExists('events');
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['group', 'show_contact_card']);
        });
    }
};
