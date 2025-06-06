<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCategory extends Model
{
    use HasFactory; 
    protected $fillable = [
        'name'
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_category_id');
    }
}
