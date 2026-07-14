<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedDay extends Model
{
    protected $fillable = [
        'date',
        'name',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Vérifie si une date est bloquée.
     */
    public static function isBlocked(string|\Carbon\Carbon $date): bool
    {
        return static::whereDate('date', $date)
            ->where('is_active', true)
            ->exists();
    }
}
