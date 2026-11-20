<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CirculationPenalty extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'patron_type_id',
        'asset_type_id',
        'lost_after_days',
    ];

    protected $casts = [
        'patron_type_id' => 'integer',
        'asset_type_id' => 'integer',
        'lost_after_days' => 'integer',
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

    /**
     * Calculate fine per day based on item unit price divided by lost_after_days.
     */
    public function calculateFinePerDay(float $unitPrice): float
    {
        $days = max(1, (int) $this->lost_after_days);

        return round(max(0, $unitPrice) / $days, 2);
    }

    /**
     * Determine whether overdue days have passed the lost_after_days threshold.
     */
    public function isLostAfterOverdueDays(int $overdueDays): bool
    {
        return $overdueDays > max(1, (int) $this->lost_after_days);
    }

    /**
     * Get descriptive dropdown label for Circulation Process.
     */
    public function getDisplayLabelAttribute(): string
    {
        $assetName = $this->assetType?->name ?? 'Asset';
        $duration = $this->loanLimit?->loan_duration_days ?? 7;
        $limit = $this->loanLimit?->max_borrow_limit ?? 3;

        return "{$assetName} ({$duration}d loan / Max {$limit} items / Lost after {$this->lost_after_days}d overdue)";
    }
}
