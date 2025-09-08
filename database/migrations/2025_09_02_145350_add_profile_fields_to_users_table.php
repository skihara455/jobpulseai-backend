<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('headline')->nullable()->after('phone');     // short title e.g. "Backend Engineer"
            $table->string('location')->nullable()->after('headline');  // city / remote
            $table->string('website')->nullable()->after('location');
            $table->string('linkedin_url')->nullable()->after('website');
            $table->string('github_url')->nullable()->after('linkedin_url');
            $table->text('bio')->nullable()->after('github_url');
            $table->string('avatar_url')->nullable()->after('bio');     // (later: file upload)
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone','headline','location','website',
                'linkedin_url','github_url','bio','avatar_url',
            ]);
        });
    }
};
