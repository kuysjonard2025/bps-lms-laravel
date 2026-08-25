<?php

namespace App\Models;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string|null $suffix
 * @property string|null $address
 * @property string|null $contact_number
 * @property string $email
 * @property string $username
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'first_name',
    'middle_name',
    'last_name',
    'suffix',
    'address',
    'contact_number',
    'username',
    'email',
    'password',
    'role',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Override default verification notification to use queues.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail);
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): string
    {
        $name = ucwords($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
        $suffix = strtoupper($this->suffix ? ' ' . $this->suffix : '');
        return trim($name . $suffix);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $name = ucwords($this->first_name . ' ' . $this->last_name);
        $initials = trim($name);

        return Str::of($initials)
            ->explode(' ')
            ->map(fn (string $segment) => Str::substr($segment, 0, 1))
            ->take(2)
            ->implode('');
    }
}

/**
 * Queued Email Verification Notification Class
 */
class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;
}
