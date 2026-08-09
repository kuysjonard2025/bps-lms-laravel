<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatronLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Explicitly set if table name is `patron_logs`.
     */
    protected $table = 'patron_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'patron_id',
        'time_in',
        'time_out',
        'log_date',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'log_date' => 'date',
    ];

    /**
     * Get the patron associated with this log.
     */
    public function patron(): BelongsTo
    {
        return $this->belongsTo(Patron::class, 'patron_id');
    }
}
