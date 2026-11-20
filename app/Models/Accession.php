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
        'acquisition_id',
        'accession_number',
        'condition',
        'status',
        'remarks',
    ];

    protected function barcode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->accession_number,
        );
    }

    public function catalog()
    {
        return $this->hasOneThrough(
            Catalog::class,
            Acquisition::class,
            'id', // Foreign key on acquisitions table...
            'id', // Foreign key on catalogs table...
            'acquisition_id', // Local key on accessions table...
            'catalog_id' // Local key on acquisitions table...
        );
    }

    public function acquisition(): BelongsTo
    {
        return $this->belongsTo(Acquisition::class);
    }

    public function circulations()
    {
        return $this->hasMany(Circulation::class);
    }
}
