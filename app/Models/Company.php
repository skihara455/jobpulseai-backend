<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;   // <-- add
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;                                      // <-- add

    protected $fillable = [
        'owner_id',
        'name',
        'website',
        'location',
        'industry',
        'size',
        'description',
        'logo_path',
        'logo_url',
        'linkedin_url',
        'twitter_url',
    ];

    /** The employer/admin who owns this company */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Jobs associated with this company */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'company_id');
    }
}
