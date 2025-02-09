<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerCertification extends Model
{
    protected $table = 'job_seeker_certifications';
    protected $fillable = [
        'jobseeker_id',
        'name',
        'issuing_organization',
        'issue_date',
        'expiry_date',
        'no_expiry',
        'credential_id',
        'credential_url'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'no_expiry' => 'boolean'
    ];

    public function jobseeker(): BelongsTo
    {
        return $this->belongsTo(Jobseeker::class, 'jobseeker_id');
    }
} 