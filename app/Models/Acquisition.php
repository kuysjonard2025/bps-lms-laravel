<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Acquisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_id',
        'vendor_id',
        'acquisition_number',
        'transaction_number',
        'quantity',
        'unit_cost',
        'received_date',
        'remarks',
    ];

    protected $casts = [
        'received_date' => 'date',
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function accessions(): HasMany
    {
        return $this->hasMany(Accession::class);
    }
}
