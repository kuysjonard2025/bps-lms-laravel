<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetType extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
    ];

    public function circulationPolicies(): HasMany
    {
        return $this->hasMany(CirculationPolicy::class);
    }
}
