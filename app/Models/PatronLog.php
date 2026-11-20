<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatronLog extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'patron_logs';

    protected $fillable = [
        'patron_id',
        'time_in',
        'time_out',
        'log_date',
    ];

    protected $casts = [
        'time_in'  => 'datetime',
        'time_out' => 'datetime',
        'log_date' => 'date',
    ];

    public function patron(): BelongsTo
    {
        return $this->belongsTo(Patron::class, 'patron_id');
    }
}
