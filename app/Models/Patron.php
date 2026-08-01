<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patron extends Model
{
    protected $fillable = [
        'patron_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'address',
        'contact_number',
        'email',
        'patron_type_id',
        'grade_level_id',
        'section_id',
        'status',
    ];

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function patronType(): BelongsTo
    {
        return $this->belongsTo(PatronType::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
    }
}
