<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accession extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'catalog_id',
        'acquisition_id',
        'accession_number',
        'batch_number',
        'call_number',
        'condition',
        'status',
        'acquired_date',
        'remarks',
    ];

    protected $casts = [
        'acquired_date' => 'date',
    ];

    /**
     * Accessor to treat accession_number as the barcode value.
     */
    protected function barcode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->accession_number,
        );
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function acquisition(): BelongsTo
    {
        return $this->belongsTo(Acquisition::class);
    }
}
