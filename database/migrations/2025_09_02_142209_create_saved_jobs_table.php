<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // who saved
            $table->unsignedBigInteger('job_id');  // which job (job_listings.id)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_id')->references('id')->on('job_listings')->onDelete('cascade');

            $table->unique(['user_id', 'job_id']); // prevent duplicates
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_jobs');
    }
};
