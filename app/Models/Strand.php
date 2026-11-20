<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Strand extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
    ];
}
