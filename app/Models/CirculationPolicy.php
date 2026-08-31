<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CirculationPolicy extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'patron_type_id',
        'asset_type_id',
        'max_borrow_limit',
        'loan_duration_days',
        'fine_per_day',
        'max_fine_amount',
        'is_active',
    ];

    protected $casts = [
        'patron_type_id' => 'integer',
        'asset_type_id' => 'integer',
        'max_borrow_limit' => 'integer',
        'loan_duration_days' => 'integer',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get descriptive dropdown label for Circulation Process
     */
    public function getDisplayLabelAttribute(): string
    {
        return "{$this->name} ({$this->loan_duration_days} days / Max {$this->max_borrow_limit} items)";
    }
}
