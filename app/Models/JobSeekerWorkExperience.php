<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerWorkExperience extends Model
{
    protected $table = 'job_seeker_work_experiences';
    protected $fillable = [
        'jobseeker_id',
        'title',
        'company_name',
        'joined_date',
        'end_date',
        'currently_working',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'end_date' => 'date',
        'currently_working' => 'boolean',
    ];

    public function jobseeker(): BelongsTo
    {
        return $this->belongsTo(Jobseeker::class, 'jobseeker_id');
    }
} 