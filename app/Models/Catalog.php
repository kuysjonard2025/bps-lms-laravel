<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Catalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'asset_type_id',
        'publisher_id',
        'general_reference_id',
        'title',
        'isbn_issn',
        'edition',
        'publication_year',
        'description',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function generalReference(): BelongsTo
    {
        return $this->belongsTo(GeneralReference::class);
    }

    public function acquisitions(): HasMany
    {
        return $this->hasMany(Acquisition::class);
    }
}
