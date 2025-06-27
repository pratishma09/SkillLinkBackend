<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'jobseeker_id',
        'is_saved',
        'jobseeker_status',
        'applied_date',
        'meeting_time',
        'meeting_link'
    ];

    protected $casts = [
        'applied_date' => 'datetime',
        'meeting_time' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class,'project_id');
    }

    public function jobseeker()
    {
        return $this->belongsTo(Jobseeker::class,'jobseeker_id');
    }
}
