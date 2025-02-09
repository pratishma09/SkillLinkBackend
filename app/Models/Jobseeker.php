<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jobseeker extends Model
{
    protected $fillable = [
        'user_id',
        'college_id',
        'image',
        'mobile',
        'dob',
        'gender',
        'current_address',
        'permanent_address',
        'linkedin_url',
        'professional_summary',
        'skills',
        
    ];

    protected $casts = [
        'dob' => 'date',
        'skills' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function education(): HasMany
    {
        return $this->hasMany(JobSeekerEducation::class, 'jobseeker_id');
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(JobSeekerWorkExperience::class, 'jobseeker_id');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(JobSeekerCertification::class, 'jobseeker_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(JobSeekerProject::class, 'jobseeker_id');
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(User::class, 'college_id')->where('role', 'college');
    }
} 