<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            self::logAction($model, 'created', 'Telah membuat ' . class_basename($model) . ' baru.');
        });

        static::updated(function ($model) {
            self::logAction($model, 'updated', 'Telah memperbarui ' . class_basename($model) . '.', true);
        });

        static::deleted(function ($model) {
            self::logAction($model, 'deleted', 'Telah menghapus ' . class_basename($model) . '.');
        });
    }

    protected static function logAction($model, $action, $description, $logChanges = false)
    {
        if (!Auth::check()) {
            return;
        }

        $changes = null;

        if ($logChanges && $action === 'updated') {
            $changes = [
                'before' => \Illuminate\Support\Arr::except($model->getOriginal(), ['updated_at']),
                'after'  => \Illuminate\Support\Arr::except($model->getChanges(), ['updated_at']),
            ];
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => class_basename($model),
            'description' => $description,
            'changes' => $changes,
        ]);
    }
}
