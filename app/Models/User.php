<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['num_order', 'name', 'first_name', 'last_name', 'job_title', 'supervisor_id', 'signature', 'photo', 'email', 'password', 'settings', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Configuration des logs d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'first_name', 'last_name', 'email', 'job_title', 'supervisor_id', 'is_active', 'settings'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user')
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "Utilisateur '{$this->full_name}' créé",
                'updated' => "Utilisateur '{$this->full_name}' modifié",
                'deleted' => "Utilisateur '{$this->full_name}' supprimé",
                'restored' => "Utilisateur '{$this->full_name}' restauré",
                default => "Utilisateur '{$this->full_name}' {$eventName}",
            });
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function subProjects()
    {
        return $this->belongsToMany(SubProject::class)
            ->withTimestamps();
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function monthlyReports()
    {
        return $this->hasMany(MonthlyReport::class);
    }

    public function validatedReports()
    {
        return $this->hasMany(ReportValidation::class, 'validator_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot([
                'role',
                'assigned_at',
                'ended_at',
            ])
            ->withTimestamps();
    }

    /**
     * Récupère tous les logs d'activité pour cet utilisateur
     */
    public function activityLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject');
    }

    /**
     * Récupère les logs où cet utilisateur est le causer (auteur)
     */
    public function causedActivityLogs()
    {
        return \Spatie\Activitylog\Models\Activity::where('causer_type', self::class)
            ->where('causer_id', $this->id);
    }

    /**
     * Récupère le dernier log d'activité
     */
    public function latestActivityLog()
    {
        return $this->morphOne(\Spatie\Activitylog\Models\Activity::class, 'subject')->latest('created_at');
    }

    /**
     * Récupère les logs de changement de statut (activation/désactivation)
     */
    public function statusChangeLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->where('event', 'updated')
            ->whereRaw("JSON_EXTRACT(properties, '$.attributes.is_active') IS NOT NULL")
            ->orWhereRaw("JSON_EXTRACT(properties, '$.old.is_active') IS NOT NULL");
    }

    /**
     * Récupère les logs de changement de supervisor
     */
    public function supervisorChangeLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->where('event', 'updated')
            ->whereRaw("JSON_EXTRACT(properties, '$.attributes.supervisor_id') IS NOT NULL")
            ->orWhereRaw("JSON_EXTRACT(properties, '$.old.supervisor_id') IS NOT NULL");
    }

    /**
     * Récupère les logs de changement de rôle (Spatie Permission)
     */
    public function roleChangeLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->where('log_name', 'permission')
            ->orWhere('description', 'like', '%role%');
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les utilisateurs inactifs
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope pour les utilisateurs par nom complet
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Scope pour les utilisateurs d'un projet spécifique
     */
    public function scopeInProject($query, $projectId)
    {
        return $query->whereHas('projects', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        });
    }

    /**
     * Scope pour les utilisateurs d'un sous-projet spécifique
     */
    public function scopeInSubProject($query, $subProjectId)
    {
        return $query->whereHas('subProjects', function ($q) use ($subProjectId) {
            $q->where('sub_project_id', $subProjectId);
        });
    }

    /**
     * Vérifie si l'utilisateur est actif
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Vérifie si l'utilisateur est un superviseur
     */
    public function isSupervisor(): bool
    {
        return $this->subordinates()->count() > 0;
    }

    /**
     * Activer l'utilisateur avec log
     */
    public function activate(?string $reason = null)
    {
        $this->is_active = true;
        $this->save();

        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'action' => 'activate',
                'reason' => $reason,
                'activated_at' => now(),
            ])
            ->log("Utilisateur '{$this->full_name}' activé");

        return $this;
    }

    /**
     * Désactiver l'utilisateur avec log
     */
    public function deactivate(?string $reason = null)
    {
        $this->is_active = false;
        $this->save();

        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'action' => 'deactivate',
                'reason' => $reason,
                'deactivated_at' => now(),
            ])
            ->log("Utilisateur '{$this->full_name}' désactivé");

        return $this;
    }

    /**
     * Changer le superviseur avec log
     */
    public function changeSupervisor(?User $newSupervisor, ?string $reason = null)
    {
        $oldSupervisorId = $this->supervisor_id;
        $oldSupervisorName = $this->supervisor?->full_name;
        $this->supervisor_id = $newSupervisor?->id;
        $this->save();

        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'old_supervisor_id' => $oldSupervisorId,
                'old_supervisor_name' => $oldSupervisorName,
                'new_supervisor_id' => $newSupervisor?->id,
                'new_supervisor_name' => $newSupervisor?->full_name,
                'reason' => $reason,
                'changed_at' => now(),
            ])
            ->log("Superviseur de '{$this->full_name}' changé de '{$oldSupervisorName}' à '{$newSupervisor?->full_name}'");

        return $this;
    }

    /**
     * Assigner un rôle avec log
     */
    public function assignRoleWithLog($role)
    {
        $this->assignRole($role);
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'action' => 'assign_role',
                'role' => is_string($role) ? $role : $role->name,
                'assigned_at' => now(),
            ])
            ->log("Rôle '{$role}' assigné à '{$this->full_name}'");

        return $this;
    }

    /**
     * Retirer un rôle avec log
     */
    public function removeRoleWithLog($role)
    {
        $this->removeRole($role);
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'action' => 'remove_role',
                'role' => is_string($role) ? $role : $role->name,
                'removed_at' => now(),
            ])
            ->log("Rôle '{$role}' retiré de '{$this->full_name}'");

        return $this;
    }

    /**
     * Ajouter un utilisateur à un projet avec log
     */
    public function addToProject(Project $project, ?string $role = null)
    {
        $this->projects()->attach($project, ['role' => $role, 'assigned_at' => now()]);
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'action' => 'assign_to_project',
                'project_id' => $project->id,
                'project_name' => $project->name,
                'role' => $role,
                'assigned_at' => now(),
            ])
            ->log("Utilisateur '{$this->full_name}' assigné au projet '{$project->name}'");

        return $this;
    }

    /**
     * Retirer un utilisateur d'un projet avec log
     */
    public function removeFromProject(Project $project)
    {
        $this->projects()->detach($project);
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'action' => 'remove_from_project',
                'project_id' => $project->id,
                'project_name' => $project->name,
                'removed_at' => now(),
            ])
            ->log("Utilisateur '{$this->full_name}' retiré du projet '{$project->name}'");

        return $this;
    }

    /**
     * Mettre à jour les paramètres utilisateur avec log
     */
    public function updateSettings(array $newSettings, ?string $reason = null)
    {
        $oldSettings = $this->settings;
        $this->settings = array_merge($this->settings ?? [], $newSettings);
        $this->save();

        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'action' => 'update_settings',
                'old_settings' => $oldSettings,
                'new_settings' => $this->settings,
                'reason' => $reason,
                'changed_at' => now(),
            ])
            ->log("Paramètres de '{$this->full_name}' mis à jour");

        return $this;
    }

    /**
     * Récupère le nom complet de l'utilisateur
     */
    public function getFullNameAttribute(): string
    {
        $firstName = $this->first_name ?? '';
        $lastName = $this->last_name ?? '';
        $name = $this->name ?? '';

        if ($firstName && $lastName) {
            return trim($firstName.' '.$lastName);
        }

        if ($firstName) {
            return $firstName;
        }

        if ($lastName) {
            return $lastName;
        }

        return $name ?: 'Utilisateur inconnu';
    }

    /**
     * Récupère le nom complet inversé (Nom Prénom)
     */
    public function getFullNameReversedAttribute(): string
    {
        $firstName = $this->first_name ?? '';
        $lastName = $this->last_name ?? '';

        if ($firstName && $lastName) {
            return trim($lastName.' '.$firstName);
        }

        return $this->full_name;
    }

    /**
     * Récupère l'initiale de l'utilisateur
     */
    public function getInitialAttribute(): string
    {
        $firstName = $this->first_name ?? '';
        $lastName = $this->last_name ?? '';

        if ($firstName && $lastName) {
            return strtoupper($firstName[0].$lastName[0]);
        }

        if ($firstName) {
            return strtoupper($firstName[0]);
        }

        if ($lastName) {
            return strtoupper($lastName[0]);
        }

        return 'U';
    }

    /**
     * Vérifie si l'utilisateur est un administrateur (via Spatie Permission)
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Vérifie si l'utilisateur a accès à un projet spécifique
     */
    public function hasAccessToProject(Project $project): bool
    {
        return $this->projects()->where('project_id', $project->id)->exists();
    }

    /**
     * Vérifie si l'utilisateur a accès à un sous-projet spécifique
     */
    public function hasAccessToSubProject(SubProject $subProject): bool
    {
        return $this->subProjects()->where('sub_project_id', $subProject->id)->exists()
            || $this->hasAccessToProject($subProject->project);
    }

    /**
     * Fonction score pour tester la colonne settings
     * Retourne un tableau avec le score et les détails
     */
    public function score(): array
    {
        $settings = $this->settings;

        // Vérifier si les settings sont vides ou null
        if (empty($settings)) {
            return [
                'score' => 0,
                'is_empty' => true,
                'has_notifications' => false,
                'has_email_notifications' => false,
                'has_database_notifications' => false,
                'details' => 'Aucun paramètre configuré',
                'settings' => $settings,
            ];
        }

        // Vérifier si la structure notifications existe
        $hasNotifications = isset($settings['notifications']) && is_array($settings['notifications']);

        // Vérifier les notifications spécifiques
        $hasEmailNotifications = $hasNotifications && isset($settings['notifications']['email']);
        $hasDatabaseNotifications = $hasNotifications && isset($settings['notifications']['database']);

        // Calculer le score (sur 10 points)
        $score = 0;
        $details = [];

        if ($hasNotifications) {
            $score += 3; // 3 points pour avoir une structure notifications
            $details[] = 'Structure notifications présente';

            if ($hasEmailNotifications) {
                $score += 3; // 3 points pour avoir email
                $details[] = 'Notification email configurée';

                if ($settings['notifications']['email'] === true) {
                    $score += 2; // 2 points supplémentaires si activé
                    $details[] = 'Notification email activée';
                }
            }

            if ($hasDatabaseNotifications) {
                $score += 3; // 3 points pour avoir database
                $details[] = 'Notification database configurée';

                if ($settings['notifications']['database'] === true) {
                    $score += 2; // 2 points supplémentaires si activé
                    $details[] = 'Notification database activée';
                }
            }
        } else {
            $details[] = 'Aucune notification configurée';
        }

        return [
            'score' => min($score, 10), // Max 10 points
            'is_empty' => false,
            'has_notifications' => $hasNotifications,
            'has_email_notifications' => $hasEmailNotifications,
            'has_database_notifications' => $hasDatabaseNotifications,
            'email_enabled' => $hasEmailNotifications ? $settings['notifications']['email'] : false,
            'database_enabled' => $hasDatabaseNotifications ? $settings['notifications']['database'] : false,
            'details' => implode(' - ', $details),
            'settings' => $settings,
        ];
    }

    /**
     * Vérifie si les settings sont vides
     */
    public function hasEmptySettings(): bool
    {
        return empty($this->settings);
    }

    /**
     * Vérifie si les notifications sont configurées
     */
    public function hasNotifications(): bool
    {
        if (empty($this->settings)) {
            return false;
        }

        return isset($this->settings['notifications']) && is_array($this->settings['notifications']);
    }

    /**
     * Vérifie si les notifications email sont activées
     */
    public function hasEmailNotifications(): bool
    {
        if (empty($this->settings)) {
            return false;
        }

        return isset($this->settings['notifications']['email'])
            && $this->settings['notifications']['email'] === true;
    }

    /**
     * Vérifie si les notifications database sont activées
     */
    public function hasDatabaseNotifications(): bool
    {
        if (empty($this->settings)) {
            return false;
        }

        return isset($this->settings['notifications']['database'])
            && $this->settings['notifications']['database'] === true;
    }

    /**
     * Récupère le score sous forme de pourcentage
     */
    public function scorePercentage(): int
    {
        return $this->score()['score'] * 10;
    }

    /**
     * Récupère le niveau de configuration
     */
    public function getConfigurationLevel(): string
    {
        $score = $this->score()['score'];

        if ($score === 0) {
            return 'Aucune configuration';
        } elseif ($score <= 3) {
            return 'Configuration minimale';
        } elseif ($score <= 6) {
            return 'Configuration partielle';
        } elseif ($score <= 8) {
            return 'Configuration avancée';
        } else {
            return 'Configuration complète';
        }
    }

    /**
     * Récupère la couleur associée au niveau de configuration
     */
    public function getConfigurationColor(): string
    {
        $score = $this->score()['score'];

        if ($score === 0) {
            return 'danger';
        } elseif ($score <= 3) {
            return 'warning';
        } elseif ($score <= 6) {
            return 'info';
        } elseif ($score <= 8) {
            return 'primary';
        } else {
            return 'success';
        }
    }
}
