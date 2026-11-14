<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harvest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'flock_id',
        'harvest_date',
        'total_chickens_sold',
        'total_weight_kg',
        'buyer_name',
        'chicken_price_per_kg',
        'feed_price_per_kg',
        'total_feed_cost',
        'gross_revenue',
        'net_profit',
        'revenue',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harvest_date' => 'date',
            'total_chickens_sold' => 'integer',
            'total_weight_kg' => 'decimal:2',
            'chicken_price_per_kg' => 'decimal:2',
            'feed_price_per_kg' => 'decimal:2',
            'total_feed_cost' => 'decimal:2',
            'gross_revenue' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'revenue' => 'decimal:2',
        ];
    }

    /**
     * Get the flock that owns the harvest record.
     */
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
}
