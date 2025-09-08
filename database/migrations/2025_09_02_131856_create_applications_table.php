<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('job_id');   // job_listings.id
            $table->unsignedBigInteger('user_id');  // users.id (seeker)

            $table->text('cover_letter')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, rejected

            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('job_listings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['job_id', 'user_id']); // prevent duplicate applications
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
