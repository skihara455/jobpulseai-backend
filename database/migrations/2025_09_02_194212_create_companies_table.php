<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // If each employer user owns exactly one company, keep this unique
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('name');
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->string('industry')->nullable();
            $table->string('size')->nullable(); // e.g. "1-10", "11-50"
            $table->text('description')->nullable();

            // media
            $table->string('logo_path')->nullable(); // storage path (public disk)
            $table->string('logo_url')->nullable();  // public URL (/storage/...)

            // socials (optional)
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();

            $table->timestamps();

            $table->index(['name', 'industry', 'location']);
            $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            $table->unique('owner_id'); // one company per owner user (delete this line if you want many)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
