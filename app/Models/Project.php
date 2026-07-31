<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'description',
        'manager_id',
        'start_date',
        'end_date',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Configuration des logs d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'name', 'description', 'manager_id', 'start_date', 'end_date', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('project')
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => "Projet {$this->name} créé",
                'updated' => "Projet {$this->name} modifié",
                'deleted' => "Projet {$this->name} supprimé",
                'restored' => "Projet {$this->name} restauré",
                default => "Projet {$this->name} {$eventName}",
            });
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'role',
                'assigned_at',
                'ended_at'
            ])
            ->withTimestamps();
    }

    public function subProjects()
    {
        return $this->hasMany(SubProject::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Récupère tous les logs d'activité pour ce projet
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
     * Récupère les logs de changement de statut
     */
    public function statusChangeLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->where('event', 'updated')
            ->whereRaw("JSON_EXTRACT(properties, '$.attributes.status') IS NOT NULL")
            ->orWhereRaw("JSON_EXTRACT(properties, '$.old.status') IS NOT NULL");
    }

    /**
     * Récupère les logs de changement de manager
     */
    public function managerChangeLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->where('event', 'updated')
            ->whereRaw("JSON_EXTRACT(properties, '$.attributes.manager_id') IS NOT NULL")
            ->orWhereRaw("JSON_EXTRACT(properties, '$.old.manager_id') IS NOT NULL");
    }

    /**
     * Scope pour les projets actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les projets terminés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope pour les projets en cours
     */
    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    /**
     * Scope pour les projets en pause
     */
    public function scopeOnHold($query)
    {
        return $query->where('status', 'on_hold');
    }

    /**
     * Scope pour les projets annulés
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope pour les projets dont la date de fin est dépassée
     */
    public function scopeOverdue($query)
    {
        return $query->where('end_date', '<', now())
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled');
    }

    /**
     * Vérifie si le projet est en retard
     */
    public function isOverdue(): bool
    {
        return $this->end_date && $this->end_date->isPast()
            && !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Vérifie si le projet est actif
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Changer le statut du projet avec log personnalisé
     */
    public function changeStatus(string $newStatus, string $reason = null)
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->save();

        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason,
                'changed_at' => now()
            ])
            ->log("Statut du projet changé de {$oldStatus} à {$newStatus}");

        return $this;
    }

    /**
     * Changer le manager du projet avec log personnalisé
     */
    public function changeManager(User $newManager, string $reason = null)
    {
        $oldManagerId = $this->manager_id;
        $oldManagerName = $this->manager?->name;
        $this->manager_id = $newManager->id;
        $this->save();

        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'old_manager_id' => $oldManagerId,
                'old_manager_name' => $oldManagerName,
                'new_manager_id' => $newManager->id,
                'new_manager_name' => $newManager->name,
                'reason' => $reason,
                'changed_at' => now()
            ])
            ->log("Manager du projet changé de {$oldManagerName} à {$newManager->name}");

        return $this;
    }

    /**
     * Marquer le projet comme terminé avec log
     */
    public function complete(string $reason = null)
    {
        return $this->changeStatus('completed', $reason);
    }

    /**
     * Marquer le projet comme en pause avec log
     */
    public function pause(string $reason = null)
    {
        return $this->changeStatus('on_hold', $reason);
    }

    /**
     * Réactiver le projet avec log
     */
    public function reactivate(string $reason = null)
    {
        return $this->changeStatus('active', $reason);
    }

    /**
     * Annuler le projet avec log
     */
    public function cancel(string $reason = null)
    {
        return $this->changeStatus('cancelled', $reason);
    }
}
