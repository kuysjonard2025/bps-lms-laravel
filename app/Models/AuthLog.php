<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthLog extends Model
{
    use HasFactory, LogsActivity;

    public $timestamps = false; // Using custom logged_at timestamp

    protected $fillable = [
        'user_id',
        'email',
        'event',
        'ip_address',
        'user_agent',
        'guard',
        'logged_at',
        'metadata',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
