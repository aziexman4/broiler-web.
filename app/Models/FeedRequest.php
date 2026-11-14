<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'flock_id',
        'feed_type',
        'amount_kg',
        'status',
    ];

    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }
}
