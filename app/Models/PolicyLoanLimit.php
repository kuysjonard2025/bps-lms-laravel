<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyLoanLimit extends Model
{
    protected $fillable = [
        'patron_type_id',
        'max_borrow_limit',
        'loan_duration_days',
    ];

    public function patronType(): BelongsTo
    {
        return $this->belongsTo(PatronType::class);
    }
}
