<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Acquisition extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'catalog_id',
        'vendor_id',
        'acquisition_number',
        'transaction_number',
        'status',
        'delivery_type',
        'quantity_ordered',
        'quantity_received',
        'unit_cost',
        'delivery_date',
        'received_date',
        'remarks',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'received_date' => 'date',
        'quantity_ordered' => 'integer',
        'quantity_received' => 'integer',
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

    protected function totalCost(): Attribute
    {
        return Attribute::make(
            get: fn () => round(((float) $this->quantity_ordered) * ((float) $this->unit_cost), 2),
        );
    }
}
