<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerProject extends Model
{
    protected $table = 'job_seeker_projects';
    protected $fillable = [
        'jobseeker_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'currently_working',
        'project_url',
        'github_url'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'currently_working' => 'boolean'
    ];

    public function jobseeker(): BelongsTo
    {
        return $this->belongsTo(Jobseeker::class, 'jobseeker_id');
    }
} 