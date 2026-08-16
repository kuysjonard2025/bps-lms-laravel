<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Circulation extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patron_id',
        'accession_id',
        'processed_by',
        'borrowed_at',
        'due_at',
        'returned_at',
        'renewal_count',
        'transaction_number',
        'fine_amount',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'renewal_count' => 'integer',
            'fine_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the patron who borrowed the item.
     */
    public function patron(): BelongsTo
    {
        return $this->belongsTo(Patron::class);
    }

    /**
     * Get the specific accession item being borrowed.
     */
    public function accession(): BelongsTo
    {
        return $this->belongsTo(Accession::class);
    }

    /**
     * Get the user (staff/librarian) who processed the transaction.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
