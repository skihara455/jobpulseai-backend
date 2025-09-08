<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;   // <-- Add this
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;            // <-- Include HasFactory

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // -----------------
    // Relationships
    // -----------------
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Saved jobs pivot: saved_jobs (user_id, job_id) -> job_listings
     */
    public function savedJobs()
    {
        return $this->belongsToMany(Job::class, 'saved_jobs', 'user_id', 'job_id')
                    ->withTimestamps();
    }

    // -----------------
    // Role helpers
    // -----------------
    public function isAdmin(): bool    { return optional($this->role)->name === 'admin'; }
    public function isEmployer(): bool { return optional($this->role)->name === 'employer'; }
    public function isSeeker(): bool   { return optional($this->role)->name === 'seeker'; }
    public function isMentor(): bool   { return optional($this->role)->name === 'mentor'; }

    public function abilities(): array
    {
        return [
            'admin'    => $this->isAdmin(),
            'employer' => $this->isEmployer(),
            'seeker'   => $this->isSeeker(),
            'mentor'   => $this->isMentor(),
        ];
    }
}

