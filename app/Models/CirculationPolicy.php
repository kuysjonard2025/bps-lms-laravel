<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CirculationPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'patron_type_id',
        'asset_type_id',
        'max_borrow_limit',
        'loan_duration_days',
        'max_renewals',
        'grace_period_days',
        'fine_per_day',
        'max_fine_amount',
        'is_active',
    ];

    protected $casts = [
        'patron_type_id' => 'integer',
        'asset_type_id' => 'integer',
        'max_borrow_limit' => 'integer',
        'loan_duration_days' => 'integer',
        'max_renewals' => 'integer',
        'grace_period_days' => 'integer',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function findPolicy(int $patronTypeId, int $assetTypeId): ?self
    {
        return static::query()
            ->active()
            ->where('patron_type_id', $patronTypeId)
            ->where('asset_type_id', $assetTypeId)
            ->first();
    }
}
