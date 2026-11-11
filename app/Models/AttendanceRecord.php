<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'office_id',
        'type',
        'attendance_date',
        'recorded_at',
        'latitude',
        'longitude',
        'accuracy',
        'distance',
        'qr_token',
        'ip_address',
        'user_agent',
        'status',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'recorded_at' => 'datetime',

            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy' => 'float',
            'distance' => 'float',
        ];
    }

    /**
     * Agent ayant effectué le pointage.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bureau du pointage.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}