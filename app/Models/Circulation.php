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

    // Dynamic Accessor: Check if fine is unpaid
    protected function hasUnpaidFine(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->fine_amount > 0 && ! $this->is_paid
        );
    }

    // Dynamic Accessor: Check if currently overdue
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->returned_at) {
                    return $this->returned_at->greaterThan($this->due_at);
                }
                return now()->greaterThan($this->due_at);
            }
        );
    }

    // Dynamic Accessor: Calculate total days overdue
    protected function overdueDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                $endDate = $this->returned_at ?? now();

                if ($endDate->greaterThan($this->due_at)) {
                    return (int) $this->due_at->diffInDays($endDate);
                }

                return 0;
            }
        );
    }

    // Helper: Record payment & receipt
    public function recordPayment(string $receiptNumber): bool
    {
        return $this->update([
            'receipt_number' => $receiptNumber,
            'is_paid'        => true,
        ]);
    }

    // Relationships
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
