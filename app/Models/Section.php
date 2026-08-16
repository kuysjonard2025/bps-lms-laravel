<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use LogsActivity;

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
