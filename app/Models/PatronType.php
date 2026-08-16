<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatronType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name'];

    public function patrons(): HasMany
    {
        return $this->hasMany(Patron::class);
    }

    public function circulationPolicies(): HasMany
    {
        return $this->hasMany(CirculationPolicy::class);
    }
}
