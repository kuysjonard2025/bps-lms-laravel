<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Catalog extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'author_id',
        'asset_type_id',
        'publisher_id',
        'general_reference_id',
        'title',
        'isbn_issn',
        'call_number',
        'edition',
        'publication_year',
        'description',
    ];

    protected $casts = [
        'author_id'            => 'integer',
        'asset_type_id'        => 'integer',
        'publisher_id'         => 'integer',
        'general_reference_id' => 'integer',
        'publication_year'     => 'integer',
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

    public function accessions(): HasMany
    {
        return $this->hasMany(Accession::class);
    }

    public function availableAccessions(): HasMany
    {
        return $this->hasMany(Accession::class)->whereRaw('LOWER(status) = ?', ['available']);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return $query->where(function (Builder $q) use ($term, $likeOperator) {
            $q->where('title', $likeOperator, "%{$term}%")
              ->orWhere('isbn_issn', $likeOperator, "%{$term}%")
              ->orWhere('call_number', $likeOperator, "%{$term}%")
              ->orWhereHas('author', fn (Builder $sub) => $sub->where('name', $likeOperator, "%{$term}%"))
              ->orWhereHas('publisher', fn (Builder $sub) => $sub->where('name', $likeOperator, "%{$term}%"));
        });
    }
}
