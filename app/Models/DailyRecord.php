<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class DailyRecord extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'flock_id',
        'record_date',
        'mortality',
        'culling',
        'feed_pre_starter_kg',
        'feed_starter_kg',
        'feed_finisher_kg',
        'average_weight_gram',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'mortality' => 'integer',
            'culling' => 'integer',
            'feed_pre_starter_kg' => 'decimal:2',
            'feed_starter_kg' => 'decimal:2',
            'feed_finisher_kg' => 'decimal:2',
            'average_weight_gram' => 'integer',
        ];
    }

    /**
     * Get the total feed consumed across all phases.
     * This provides backward compatibility for $record->feed_consumed_kg
     */
    public function getFeedConsumedKgAttribute()
    {
        return $this->feed_pre_starter_kg + $this->feed_starter_kg + $this->feed_finisher_kg;
    }

    /**
     * Get the flock that owns the daily record.
     */
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
}
