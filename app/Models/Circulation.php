<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Circulation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'patron_id',
        'accession_id',
        'user_id',
        'borrowed_at',
        'due_at',
        'returned_at',
        'fine_amount',
        'is_paid',
        'receipt_number',
        'status',
        'condition',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at'      => 'datetime',
            'returned_at' => 'datetime',
            'fine_amount' => 'decimal:2',
            'is_paid'     => 'boolean',
        ];
    }

    protected function hasUnpaidFine(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->fine_amount > 0 && ! $this->is_paid
        );
    }

    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->due_at) {
                    return false;
                }

                $referenceDate = $this->returned_at ?? now();
                return $referenceDate->greaterThan($this->due_at);
            }
        );
    }

    protected function overdueDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->due_at) {
                    return 0;
                }

                $endDate = $this->returned_at ?? now();

                if ($endDate->greaterThan($this->due_at)) {
                    return (int) $this->due_at->diffInDays($endDate);
                }

                return 0;
            }
        );
    }

    public function recordPayment(string $receiptNumber): bool
    {
        return $this->update([
            'receipt_number' => strtoupper(trim($receiptNumber)),
            'is_paid'        => true,
        ]);
    }

    public function patron(): BelongsTo
    {
        return $this->belongsTo(Patron::class);
    }

    public function accession(): BelongsTo
    {
        return $this->belongsTo(Accession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
