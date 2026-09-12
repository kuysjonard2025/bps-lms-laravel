<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CirculationPenalty extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'patron_type_id',
        'asset_type_id',
        'fine_per_day',
        'max_fine_amount',
        'is_active',
    ];

    protected $casts = [
        'patron_type_id' => 'integer',
        'asset_type_id' => 'integer',
        'fine_per_day' => 'decimal:2',
        'max_fine_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function patronType(): BelongsTo
    {
        return $this->belongsTo(PatronType::class);
    }

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }

    public function loanLimit(): HasOne
    {
        return $this->hasOne(PolicyLoanLimit::class, 'patron_type_id', 'patron_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get descriptive dropdown label for Circulation Process
     */
    public function getDisplayLabelAttribute(): string
    {
        $duration = $this->loanLimit?->loan_duration_days ?? 7;
        $limit = $this->loanLimit?->max_borrow_limit ?? 3;

        return "{$this->name} ({$duration} days / Max {$limit} items)";
    }
}
