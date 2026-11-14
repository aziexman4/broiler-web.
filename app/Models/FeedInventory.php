<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class FeedInventory extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'feed_inventories';

    protected $fillable = [
        'transaction_date',
        'feed_name',
        'transaction_type',
        'quantity_kg',
        'price_per_kg',
        'notes',
        'flock_id',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'quantity_kg' => 'decimal:2',
            'price_per_kg' => 'integer',
        ];
    }

    /**
     * Get the flock associated with the feed inventory record.
     */
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
}
