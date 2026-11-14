<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'flock_id',
        'base_salary',
        'bonus_amount',
        'total_paid',
        'payment_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'base_salary' => 'integer',
            'bonus_amount' => 'integer',
            'total_paid' => 'integer',
        ];
    }

    /**
     * Get the user / worker associated with the payroll.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the flock associated with the payroll.
     */
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
}
