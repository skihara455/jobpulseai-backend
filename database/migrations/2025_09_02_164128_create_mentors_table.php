<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentors', function (Blueprint $table) {
            $table->id();

            // Nullable link to a platform user; if the user is deleted, set NULL
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->string('name');
            $table->string('headline')->nullable();      // e.g., "Senior Data Scientist"
            $table->text('bio')->nullable();

            // Simple CSV tags: "AI,ML,Backend"
            $table->string('expertise')->nullable();

            $table->string('location')->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('avatar_url')->nullable();

            $table->timestamps();

            // Helpful composite index for lookups/filters
            $table->index(['name', 'expertise', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentors');
    }
};
