<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Activity extends Model
{
    use LogsActivity;

    protected $fillable = [
        'titre',
        'user_id',
        'project_id',
        'sub_project_id',
        'activity_type_id',
        'activity_date',
        'start_time',
        'end_time',
        'duration',
        'description',
        'status',
        'rejection_reason',
        'submitted_at',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'submitted_at' => 'datetime',
        'duration' => 'decimal:2',
    ];

    /**
     * Configuration des logs d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity')
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Nouvelle activité créée',
                'updated' => 'Activité modifiée',
                'deleted' => 'Activité supprimée',
                default => "Activité {$eventName}",
            });
    }

    public function scopeCurrentMonth($query)
    {
        return $query
            ->whereMonth('activity_date', now())
            ->whereYear('activity_date', now());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function subProject(): BelongsTo
    {
        return $this->belongsTo(SubProject::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function monthlyReport()
    {
        return $this->belongsTo(MonthlyReport::class);
    }

    /**
     * Récupère tous les logs d'activité
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
     * Récupère les logs par statut
     */
    public function activityLogsByStatus($status)
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->where('properties->attributes->status', $status)
            ->orWhere('properties->old->status', $status);
    }

    /**
     * Récupère les logs de changement de statut
     */
    public function statusChangeLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->where('event', 'updated')
            ->whereRaw("JSON_EXTRACT(properties, '$.attributes.status') IS NOT NULL")
            ->orWhereRaw("JSON_EXTRACT(properties, '$.old.status') IS NOT NULL");
    }
}
