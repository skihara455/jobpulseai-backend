<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Eager-load to avoid N+1 and ensure role accessors work consistently.
     */
    protected $with = ['role'];

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
     * Attribute casts
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed', // auto-hash on set
        ];
    }

    /* ----------------- Mutators / Normalizers ----------------- */

    /**
     * Normalize email (trim + lowercase) to make login non–case-sensitive.
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = Str::of($value)->trim()->lower();
    }

    /* ----------------- Relationships ----------------- */

    public function role(): BelongsTo
    {
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

    protected function roleNameValue(): ?string
    {
        $name = $this->role->name ?? null;
        return $name ? Str::of($name)->lower()->toString() : null;
    }

    public function isAdmin(): bool
    {
        return $this->roleNameValue() === 'admin';
    }

    public function isEmployer(): bool
    {
        return $this->roleNameValue() === 'employer';
    }

    public function isSeeker(): bool
    {
        return $this->roleNameValue() === 'seeker';
    }

    public function isMentor(): bool
    {
        return $this->roleNameValue() === 'mentor';
    }

    /**
     * Boolean flags the frontend can use for quick gating.
     * (Separate from Sanctum token abilities.)
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
        // Keep original case if present, else null
        return $this->role->name ?? null;
    }

    public function getAbilitiesAttribute(): array
    {
        return $this->abilities();
    }
}
