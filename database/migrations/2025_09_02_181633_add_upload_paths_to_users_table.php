<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // store the internal storage paths (e.g. "avatars/abc.jpg", "resumes/xyz.pdf")
            $table->string('avatar_path')->nullable()->after('avatar_url');
            $table->string('resume_path')->nullable()->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_path', 'resume_path']);
        });
    }
};
