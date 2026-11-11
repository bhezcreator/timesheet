<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'name',
        'day_of_week',
        'start_time',
        'end_time',
        'grace_period',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'grace_period' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Bureau associé à cet horaire.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}