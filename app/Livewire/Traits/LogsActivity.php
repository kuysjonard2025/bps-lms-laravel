<?php

namespace App\Livewire\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        // Handle Record Creation
        static::created(function ($model) {
            static::recordActivity($model, 'created', 'Created record');
        });

        // Handle Record Update
        static::updated(function ($model) {
            $changes = [
                'before' => array_intersect_key($model->getOriginal(), $model->getChanges()),
                'after'  => $model->getChanges(),
            ];

            // Remove noise and sensitive hidden attributes (like password, remember_token)
            $hidden = array_merge(['updated_at', 'created_at'], $model->getHidden());
            foreach ($hidden as $attribute) {
                unset($changes['before'][$attribute], $changes['after'][$attribute]);
            }

            if (!empty($changes['after'])) {
                static::recordActivity($model, 'updated', 'Updated record fields', $changes);
            }
        });

        // Handle Record Deletion
        static::deleted(function ($model) {
            static::recordActivity($model, 'deleted', 'Deleted record');
        });
    }

    protected static function recordActivity($model, string $event, string $description, array $properties = []): void
    {
        ActivityLog::create([
            'user_id'      => Auth::id(),
            'log_name'     => strtolower(class_basename($model)),
            'event'        => $event,
            'subject_type' => get_class($model),
            'subject_id'   => $model->getKey(),
            'description'  => $description . ' (' . class_basename($model) . " #{$model->getKey()})",
            'properties'   => $properties ?: null,
            'ip_address'   => request()->ip(),
            'created_at'   => now(),
        ]);
    }
}
