<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowthSampling extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'flock_id',
        'sampling_date',
        'week_number',
        'average_weight_g',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sampling_date' => 'date',
            'week_number' => 'integer',
            'average_weight_g' => 'integer',
        ];
    }

    /**
     * Get the flock that owns the growth sampling.
     */
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
}
