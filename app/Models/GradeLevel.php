<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeLevel extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
