<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patron extends Model
{
    protected $fillable = [
        'patron_id',
        'first_name',
        'middle_name',
        'last_name',
        'prefix',
        'type',
        'address',
        'contact_number',
        'email',
        'grade_level_id',
        'section_id',
        'status',
    ];

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
