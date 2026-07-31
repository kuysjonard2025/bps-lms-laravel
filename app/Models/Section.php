<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'name',
        'code',
        'grade_level_id',
    ];

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }
}
