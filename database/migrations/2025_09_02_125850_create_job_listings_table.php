<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title');
            $table->string('location')->nullable();     // e.g., Nairobi / Remote
            $table->string('type')->nullable();         // full-time, part-time, contract, remote
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();

            $table->string('tags')->nullable();         // comma-separated tags
            $table->text('description');

            $table->string('status')->default('open');  // open, closed, draft

            $table->timestamps();

            $table->index(['employer_id', 'status']);
            $table->index(['title', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};

