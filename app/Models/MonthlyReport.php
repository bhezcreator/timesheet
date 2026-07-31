<?php

namespace App\Models;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MonthlyReport extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'project_ids',
        'report_date',
        'objectives',
        'achievements',
        'next_actions',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'submitted_at' => 'datetime',
        'project_ids' => 'array',
    ];

    /**
     * Configuration des logs d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'objectives', 'achievements', 'next_actions', 'report_date', 'project_ids'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('monthly_report')
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'Nouveau rapport mensuel créé',
                'updated' => 'Rapport mensuel modifié',
                'submitted' => 'Rapport mensuel soumis',
                'validated' => 'Rapport mensuel validé',
                'rejected' => 'Rapport mensuel rejeté',
                'deleted' => 'Rapport mensuel supprimé',
                'restored' => 'Rapport mensuel restauré',
                default => "Rapport mensuel {$eventName}",
            });
    }

    /**
     * Enregistrement de la collection de médias (Fichiers joints)
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validation()
    {
        // Ajoutez 'monthly_report_id' de manière stricte
        return $this->hasOne(ReportValidation::class, 'monthly_report_id');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Scope pour filtrer les rapports par projet
     * Utilisation : MonthlyReport::forProject('all')->get() ou forProject(5)->get()
     */
    public function scopeForProject($query, $projectId)
    {
        if ($projectId === 'all' || empty($projectId)) {
            return $query->where(function ($q) {
                $q->whereNull('project_ids')->orWhere('project_ids', '');
            });
        }

        return $query->where('project_ids', $projectId);
    }

    /**
     * Relation optionnelle avec le Projet si un ID unique est spécifié.
     * Sécurisé : On ne l'exécute QUE si project_ids est un identifiant numérique valide.
     */
    public function project(): BelongsTo
    {
        // Si le champ est vide ou est un tableau, on renvoie une relation vide de sécurité
        if (empty($this->project_ids) || is_array($this->project_ids)) {
            return $this->belongsTo(Project::class, 'id')->whereRaw('1 = 0');
        }

        return $this->belongsTo(Project::class, 'project_ids');
    }

    /**
     * Acesseur pour générer le titre dynamique du rapport
     * Version 100% étanche pour éviter les crashs de HigherOrderTapProxy
     */
    protected function fullTitle(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function () {
                if (!isset($this->attributes['month']) || !isset($this->attributes['year'])) {
                    return "Rapport d'activité";
                }

                $monthName = \Carbon\Carbon::createFromDate(
                    (int) $this->attributes['year'],
                    (int) $this->attributes['month'],
                    1
                )->translatedFormat('F');

                $baseTitle = "Rapport du mois de " . ucfirst($monthName) . " {$this->attributes['year']}";
                $projectField = $this->attributes['project_ids'] ?? '';

                // Rapport "all"
                if (empty($projectField) || $projectField === 'all' || $projectField === '""' || $projectField === '[]') {
                    return $baseTitle . " (tous les projets)";
                }

                if ($projectField) {
                    $projectId = $this->extractProjectId($projectField);
                    // Rapport projet spécifique
                    $project = Project::where('id', $projectId)->first();
                    if ($project) {
                        return $baseTitle . " concernant le projet : {$project->name}";
                    }
                }

                return $baseTitle . " (projet : {$projectField})";
            }
        );
    }

    /**
     * Extrait l'ID du projet à partir du champ project_ids
     * Gère les différents formats : string, int, JSON, etc.
     */
    private function extractProjectId($projectField)
    {
        // Cas 1 : Vide ou null
        if (empty($projectField) || $projectField === 'null' || $projectField === '') {
            return null;
        }

        // Cas 2 : C'est déjà un nombre
        if (is_numeric($projectField)) {
            return (int) $projectField;
        }

        // Cas 3 : C'est une chaîne qui contient un nombre
        if (is_string($projectField) && is_numeric(trim($projectField, '"'))) {
            return (int) trim($projectField, '"');
        }

        // Cas 4 : C'est du JSON
        if (is_string($projectField) && str_starts_with($projectField, '[')) {
            try {
                $decoded = json_decode($projectField, true);
                if (is_array($decoded) && !empty($decoded)) {
                    // Si c'est un tableau avec un seul élément
                    if (count($decoded) === 1 && is_numeric($decoded[0])) {
                        return (int) $decoded[0];
                    }
                    // Si c'est un tableau avec plusieurs éléments (on prend le premier)
                    if (count($decoded) > 1) {
                        // Vous pouvez choisir de retourner le premier ou de faire autre chose
                        return (int) $decoded[0];
                    }
                }
            } catch (\Exception $e) {
                // Ignorer l'erreur
            }
        }

        // Cas 5 : C'est une chaîne JSON avec des guillemets simples
        if (is_string($projectField) && str_starts_with($projectField, '"')) {
            try {
                $decoded = json_decode($projectField);
                if (is_numeric($decoded)) {
                    return (int) $decoded;
                }
            } catch (\Exception $e) {
                // Ignorer l'erreur
            }
        }

        // Cas 6 : "all" ou "[]"
        if ($projectField === 'all' || $projectField === '[]' || $projectField === '"all"') {
            return null;
        }

        // Dernier recours : essayer de forcer en int
        if (is_string($projectField)) {
            $cleaned = preg_replace('/[^0-9]/', '', $projectField);
            if (!empty($cleaned)) {
                return (int) $cleaned;
            }
        }

        return null;
    }

    /**
     * Récupère tous les logs d'activité pour ce rapport
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
     * Récupère les logs de soumission
     */
    public function submissionLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->where('description', 'Rapport mensuel soumis');
    }

    /**
     * Récupère les logs de validation
     */
    public function validationLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->where('description', 'Rapport mensuel validé')
            ->orWhere('description', 'Rapport mensuel rejeté');
    }

    /**
     * Scope pour les rapports soumis
     */
    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    /**
     * Scope pour les rapports non soumis
     */
    public function scopeNotSubmitted($query)
    {
        return $query->whereNull('submitted_at');
    }

    /**
     * Scope pour les rapports par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour les rapports d'un mois spécifique
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Scope pour les rapports récents
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Soumettre le rapport avec log personnalisé
     */
    public function submit()
    {
        $this->submitted_at = now();
        $this->status = 'submitted';
        $this->save();

        // Log personnalisé de soumission
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'submitted_at' => now(),
                'month' => $this->month,
                'year' => $this->year,
                'project_ids' => $this->project_ids
            ])
            ->log('Rapport mensuel soumis');

        return $this;
    }

    /**
     * Valider le rapport avec log personnalisé
     */
    public function validateReport($validatorId, $comment = null)
    {
        $this->status = 'validated';
        $this->save();

        // Log personnalisé de validation
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'validator_id' => $validatorId,
                'comment' => $comment,
                'validated_at' => now()
            ])
            ->log('Rapport mensuel validé');

        return $this;
    }

    /**
     * Rejeter le rapport avec log personnalisé
     */
    public function rejectReport($validatorId, $reason)
    {
        $this->status = 'rejected';
        $this->save();

        // Log personnalisé de rejet
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'validator_id' => $validatorId,
                'reason' => $reason,
                'rejected_at' => now()
            ])
            ->log('Rapport mensuel rejeté');

        return $this;
    }
}
