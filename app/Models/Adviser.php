<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adviser extends Model
{
    use LogsActivity;

    protected $fillable = [
        'department_id',
        'first_name',
        'middle_name',
        'last_name',
        'contact_number',
        'email',
    ];

    protected $casts = [
        'department_id' => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getFullNameAttribute(): string
    {
        $middle = $this->middle_name ? " {$this->middle_name}" : '';

        return trim("{$this->first_name}{$middle} {$this->last_name}");
    }
}
