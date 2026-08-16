<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'address',
    ];
}
