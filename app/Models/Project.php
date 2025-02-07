<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'posted_by', // user_id of the company
        'type_of_project',
        'status',
        'requirements',
        'deadline',
        'skills_required'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'skills_required' => 'array',
        'requirements' => 'array'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by')->where('role', 'company');
    }
} 