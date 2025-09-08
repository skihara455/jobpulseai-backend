<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;   // <-- add
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Job extends Model
{
    use HasFactory;                                      // <-- add

    protected $table = 'job_listings';

    protected $fillable = [
        'employer_id',
        'company_id',   // link to companies table
        'title',
        'location',
        'type',         // e.g. full-time, part-time, remote
        'salary_min',
        'salary_max',
        'tags',         // comma-separated (keep as string for now)
        'description',
        'status',       // open, closed, draft
    ];

    protected $casts = [
        'salary_min' => 'integer',
        'salary_max' => 'integer',
    ];

    // -----------------
    // Relationships
    // -----------------

    /** Employer who posted the job */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    /** Company associated with the job (optional) */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /** Applications submitted for this job */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_id');
    }

    /** Users who saved this job (pivot: saved_jobs) */
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_jobs', 'job_id', 'user_id')
                    ->withTimestamps();
    }

    // -----------------
    // Accessors
    // -----------------
    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'open';
    }

    // -----------------
    // Query Scopes
    // -----------------

    /** Scope: only open jobs */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /** Basic search across key fields */
    public function scopeSearch($query, ?string $term)
    {
        if (!$term) return $query;

        $like = '%' . $term . '%';
        return $query->where(function ($q) use ($like) {
            $q->where('title', 'like', $like)
              ->orWhere('location', 'like', $like)
              ->orWhere('type', 'like', $like)
              ->orWhere('tags', 'like', $like)
              ->orWhere('description', 'like', $like);
        });
    }
}
