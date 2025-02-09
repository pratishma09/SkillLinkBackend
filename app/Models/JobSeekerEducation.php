<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerEducation extends Model
{
    protected $table = 'job_seeker_education';

    protected $fillable = [
        'jobseeker_id',
        'institution',
        'board',
        'graduation_year',
        'gpa',
    ];

    protected $casts = [
        'graduation_year' => 'integer',
    ];

    public function jobseeker(): BelongsTo
    {
        return $this->belongsTo(Jobseeker::class, 'jobseeker_id');
    }
} 