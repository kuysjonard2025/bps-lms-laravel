<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'contact_person',
        'address',
        'contact_number',
        'email',
    ];

    public function acquisitions(): HasMany
    {
        return $this->hasMany(Acquisition::class);
    }
}
