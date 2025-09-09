<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'name',
        'email',
        'password', // hashed via casts() below
        'role_id',  // FK -> roles.id
    ];

    /**
     * Hidden on serialization
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casts (Laravel 12 supports the method-based casts)
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed', // auto-hash on set
        ];
    }

    /* ----------------- Relationships ----------------- */

    public function role(): BelongsTo
    {
        // resolves to App\Models\Role::class
        return $this->belongsTo(Role::class);
    }

    /**
     * Saved jobs pivot: saved_jobs (user_id, job_id) -> jobs
     */
    public function savedJobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'saved_jobs', 'user_id', 'job_id')
                    ->withTimestamps();
    }

    /* ----------------- Role helpers ------------------ */

    public function isAdmin(): bool    { return $this->role?->name === 'admin'; }
    public function isEmployer(): bool { return $this->role?->name === 'employer'; }
    public function isSeeker(): bool   { return $this->role?->name === 'seeker'; }
    public function isMentor(): bool   { return $this->role?->name === 'mentor'; }

    /**
     * Programmatic ability map (handy in policies/resources)
     */
    public function abilities(): array
    {
        return [
            'admin'    => $this->isAdmin(),
            'employer' => $this->isEmployer(),
            'seeker'   => $this->isSeeker(),
            'mentor'   => $this->isMentor(),
        ];
    }

    /* -------- Expose helpers as JSON attributes ------- */

    protected $appends = ['role_name', 'abilities'];

    public function getRoleNameAttribute(): ?string
    {
        return $this->role->name ?? null; // ensure queries eager-load 'role' when listing users
    }

    public function getAbilitiesAttribute(): array
    {
        return $this->abilities();
    }
}
