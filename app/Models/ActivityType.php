<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ActivityType extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Configuration des logs d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'color', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity_type')
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'Nouveau type d\'activité créé',
                'updated' => 'Type d\'activité modifié',
                'deleted' => 'Type d\'activité supprimé',
                'restored' => 'Type d\'activité restauré',
                default => "Type d'activité {$eventName}",
            });
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Récupère tous les logs d'activité pour ce type
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
     * Scope pour les types actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les types inactifs
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
