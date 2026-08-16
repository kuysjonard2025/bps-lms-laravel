<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
    ];

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
