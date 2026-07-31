<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SubProject extends Model
{
    use LogsActivity;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'status'
    ];

    /**
     * Configuration des logs d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['project_id', 'name', 'description', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('sub_project')
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => "Sous-projet '{$this->name}' créé",
                'updated' => "Sous-projet '{$this->name}' modifié",
                'deleted' => "Sous-projet '{$this->name}' supprimé",
                'restored' => "Sous-projet '{$this->name}' restauré",
                default => "Sous-projet '{$this->name}' {$eventName}",
            });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Récupère tous les logs d'activité pour ce sous-projet
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
     * Récupère les logs de changement de projet parent
     */
    public function projectChangeLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->where('event', 'updated')
            ->whereRaw("JSON_EXTRACT(properties, '$.attributes.project_id') IS NOT NULL")
            ->orWhereRaw("JSON_EXTRACT(properties, '$.old.project_id') IS NOT NULL");
    }

    /**
     * Scope pour les sous-projets actifs
     */
    public function scopeActif($query)
    {
        return $query->where('status', 'actif');
    }

    /**
     * Scope pour les sous-projets terminés
     */
    public function scopeBrouillon($query)
    {
        return $query->where('status', 'brouillon');
    }


    /**
     * Scope pour les sous-projets annulés
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'annuler');
    }

    /**
     * Scope pour les sous-projets d'un projet spécifique
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope pour les sous-projets par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Vérifie si le sous-projet est actif
     */
    public function isActif(): bool
    {
        return $this->status === 'actif';
    }

    /**
     * Vérifie si le sous-projet est terminé
     */
    public function isBrouillon(): bool
    {
        return $this->status === 'brouillon';
    }

    /**
     * Vérifie si le sous-projet est annulé
     */
    public function isCancelled(): bool
    {
        return $this->status === 'annuler';
    }

    /**
     * Changer le statut du sous-projet avec log personnalisé
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
                'project_id' => $this->project_id,
                'project_name' => $this->project?->name,
                'changed_at' => now()
            ])
            ->log("Statut du sous-projet '{$this->name}' changé de {$oldStatus} à {$newStatus}");

        return $this;
    }

    /**
     * Marquer le sous-projet comme terminé avec log
     */
    public function complete(string $reason = null)
    {
        return $this->changeStatus('completed', $reason);
    }

    /**
     * Marquer le sous-projet comme en pause avec log
     */
    public function pause(string $reason = null)
    {
        return $this->changeStatus('on_hold', $reason);
    }

    /**
     * Réactiver le sous-projet avec log
     */
    public function reactivate(string $reason = null)
    {
        return $this->changeStatus('active', $reason);
    }

    /**
     * Annuler le sous-projet avec log
     */
    public function cancel(string $reason = null)
    {
        return $this->changeStatus('cancelled', $reason);
    }

    /**
     * Changer le projet parent avec log personnalisé
     */
    public function changeProject(Project $newProject, string $reason = null)
    {
        $oldProjectId = $this->project_id;
        $oldProjectName = $this->project?->name;
        $this->project_id = $newProject->id;
        $this->save();

        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'old_project_id' => $oldProjectId,
                'old_project_name' => $oldProjectName,
                'new_project_id' => $newProject->id,
                'new_project_name' => $newProject->name,
                'reason' => $reason,
                'changed_at' => now()
            ])
            ->log("Sous-projet '{$this->name}' déplacé du projet '{$oldProjectName}' vers '{$newProject->name}'");

        return $this;
    }

    /**
     * Ajouter un utilisateur au sous-projet avec log
     */
    public function assignUser(User $user, string $role = null)
    {
        $this->users()->attach($user);

        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'role' => $role,
                'action' => 'assign_user'
            ])
            ->log("Utilisateur '{$user->name}' assigné au sous-projet '{$this->name}'");

        return $this;
    }

    /**
     * Retirer un utilisateur du sous-projet avec log
     */
    public function removeUser(User $user)
    {
        $this->users()->detach($user);

        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'action' => 'remove_user'
            ])
            ->log("Utilisateur '{$user->name}' retiré du sous-projet '{$this->name}'");

        return $this;
    }

    /**
     * Récupère le nombre d'activités du sous-projet
     */
    public function getActivitiesCountAttribute(): int
    {
        return $this->activities()->count();
    }

    /**
     * Récupère le nombre d'utilisateurs du sous-projet
     */
    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Récupère le nom complet du sous-projet avec le projet parent
     */
    public function getFullNameAttribute(): string
    {
        $projectName = $this->project?->name ?? 'Projet inconnu';
        return "{$projectName} - {$this->name}";
    }

    /**
     * Récupère le statut en texte lisible
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'actif' => 'Actif',
            'brouillon' => 'Brouillon',
            'annuler' => 'Annuler',
            default => ucfirst($this->status),
        };
    }

    /**
     * Récupère la couleur associée au statut
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'actif' => 'success',
            'brouillon' => 'info',
            'annuler' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Récupère la progression du sous-projet basée sur les activités
     */
    public function getProgressAttribute(): float
    {
        $totalActivities = $this->activities()->count();
        if ($totalActivities === 0) {
            return 0;
        }

        $completedActivities = $this->activities()
            ->where('status', 'completed')
            ->count();

        return round(($completedActivities / $totalActivities) * 100, 2);
    }
}
