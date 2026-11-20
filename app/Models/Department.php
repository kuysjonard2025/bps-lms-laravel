<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
    ];

    public function advisers(): HasMany
    {
        return $this->hasMany(Adviser::class);
    }
}
