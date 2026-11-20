<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'type',
        'company_name',
        'company_address',
        'contact_first_name',
        'contact_middle_name',
        'contact_last_name',
        'contact_number',
        'contact_email',
    ];

    public function acquisitions(): HasMany
    {
        return $this->hasMany(Acquisition::class);
    }

    /**
     * Get the contact person's full name formatted cleanly.
     */
    public function getContactFullNameAttribute(): string
    {
        $middle = $this->contact_middle_name ? ' ' . $this->contact_middle_name : '';
        return trim("{$this->contact_first_name}{$middle} {$this->contact_last_name}");
    }

    /**
     * Scope a query to search vendors by type, company name, contact name, email, or phone.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return $query->where(function (Builder $q) use ($term, $likeOperator) {
            $q->where('type', $likeOperator, "%{$term}%")
              ->orWhere('company_name', $likeOperator, "%{$term}%")
              ->orWhere('company_address', $likeOperator, "%{$term}%")
              ->orWhere('contact_first_name', $likeOperator, "%{$term}%")
              ->orWhere('contact_last_name', $likeOperator, "%{$term}%")
              ->orWhere('contact_number', $likeOperator, "%{$term}%")
              ->orWhere('contact_email', $likeOperator, "%{$term}%");
        });
    }
}
