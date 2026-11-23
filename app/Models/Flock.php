<?php

namespace App\Models;

use App\Services\FlockMetricsService;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flock extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'coop_id',
        'name',
        'breed',
        'start_date',
        'initial_population',
        'status',
        'closed_at',
        'worker_id',
        'stock_pre_starter_kg',
        'stock_starter_kg',
        'stock_finisher_kg',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'closed_at' => 'datetime',
            'initial_population' => 'integer',
        ];
    }

    /**
     * Get the total current feed stock across all phases.
     * This provides backward compatibility for $flock->current_feed_stock_kg
     */
    public function getCurrentFeedStockKgAttribute()
    {
        return $this->stock_pre_starter_kg + $this->stock_starter_kg + $this->stock_finisher_kg;
    }

    /**
     * Get the coop unit (location) that owns this flock.
     */
    public function coop(): BelongsTo
    {
        return $this->belongsTo(Coop::class);
    }

    /**
     * Get the worker assigned to this flock.
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /**
     * Get all daily records for the flock.
     */
    public function dailyRecords(): HasMany
    {
        return $this->hasMany(DailyRecord::class);
    }

    public function feedRequests(): HasMany
    {
        return $this->hasMany(FeedRequest::class);
    }

    /**
     * Get all harvests for the flock.
     */
    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }

    /**
     * Get all payrolls linked to this flock cycle.
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Calculate standardized productivity metrics for this flock.
     */
    public function metrics(): array
    {
        return FlockMetricsService::calculateForFlock($this);
    }
}
