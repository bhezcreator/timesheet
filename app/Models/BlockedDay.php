<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BlockedDay extends Model
{
    use LogsActivity;

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
     * Configuration des logs d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Journalise tous les champs
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('blocked_day')
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => "Jour bloqué ajouté : {$this->date} - {$this->name}",
                'updated' => "Jour bloqué modifié : {$this->date} - {$this->name}",
                'deleted' => "Jour bloqué supprimé : {$this->date} - {$this->name}",
                default => "Jour bloqué {$eventName}",
            });
    }

    /**
     * Vérifie si une date est bloquée.
     */
    public static function isBlocked(string|Carbon $date): bool
    {
        return static::whereDate('date', $date)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Récupère tous les jours bloqués pour une période donnée
     */
    public static function getBlockedDates(string|Carbon $startDate, string|Carbon $endDate): array
    {
        return static::whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();
    }

    /**
     * Récupère tous les logs d'activité pour ce jour bloqué
     */
    public function activityLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject');
    }

    /**
     * Récupère le dernier log d'activité
     */
    public function latestActivityLog()
    {
        return $this->morphOne(\Spatie\Activitylog\Models\Activity::class, 'subject')->latest('created_at');
    }

    /**
     * Scope pour les jours bloqués actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les jours bloqués inactifs
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope pour les jours bloqués d'un type spécifique
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les jours bloqués dans une période
     */
    public function scopeBetweenDates($query, string|Carbon $startDate, string|Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope pour les jours bloqués à partir d'une date
     */
    public function scopeFromDate($query, string|Carbon $date)
    {
        return $query->whereDate('date', '>=', $date);
    }

    /**
     * Scope pour les jours bloqués jusqu'à une date
     */
    public function scopeUntilDate($query, string|Carbon $date)
    {
        return $query->whereDate('date', '<=', $date);
    }
}
