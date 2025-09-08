<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add only if missing (safe to run repeatedly)
            if (!Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('role_id');
            }
            if (!Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->after('avatar_path');
            }
            if (!Schema::hasColumn('users', 'resume_path')) {
                $table->string('resume_path')->nullable()->after('avatar_url');
            }
            if (!Schema::hasColumn('users', 'resume_url')) {
                $table->string('resume_url')->nullable()->after('resume_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'resume_url'))  $table->dropColumn('resume_url');
            if (Schema::hasColumn('users', 'resume_path')) $table->dropColumn('resume_path');
            if (Schema::hasColumn('users', 'avatar_url'))  $table->dropColumn('avatar_url');
            if (Schema::hasColumn('users', 'avatar_path')) $table->dropColumn('avatar_path');
        });
    }
};
