<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;

class ReportValidation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'monthly_report_id',
        'validator_id',
        'decision',
        'comment',
        'validated_at'
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    /**
     * Configuration des logs d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['decision', 'comment', 'validated_at', 'validator_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('report_validation')
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'Validation de rapport effectuée',
                'updated' => 'Validation de rapport modifiée',
                'deleted' => 'Validation de rapport supprimée',
                default => "Validation de rapport {$eventName}",
            });
    }

    public function report()
    {
        return $this->belongsTo(MonthlyReport::class, 'monthly_report_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    /**
     * Récupère tous les logs d'activité pour cette validation
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
     * Scope pour les validations approuvées
     */
    public function scopeApproved($query)
    {
        return $query->where('decision', 'approuvé');
    }

    /**
     * Scope pour les validations rejetées
     */
    public function scopeRejected($query)
    {
        return $query->where('decision', 'rejeté');
    }

    /**
     * Scope pour les validations en attente
     */
    public function scopePending($query)
    {
        return $query->whereNull('decision');
    }

    /**
     * Scope pour les validations d'un validateur spécifique
     */
    public function scopeByValidator($query, $validatorId)
    {
        return $query->where('validator_id', $validatorId);
    }

    /**
     * Scope pour les validations récentes
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('validated_at', '>=', now()->subDays($days));
    }

    /**
     * Scope pour les validations d'un rapport spécifique
     */
    public function scopeForReport($query, $reportId)
    {
        return $query->where('monthly_report_id', $reportId);
    }

    /**
     * Vérifie si la validation est approuvée
     */
    public function isApproved(): bool
    {
        return $this->decision === 'approuvé';
    }

    /**
     * Vérifie si la validation est rejetée
     */
    public function isRejected(): bool
    {
        return $this->decision === 'rejeté';
    }

    /**
     * Vérifie si la validation est en attente
     */
    public function isPending(): bool
    {
        return is_null($this->decision);
    }

    /**
     * Approuver la validation avec log personnalisé
     */
    public function approve(string $comment = null)
    {
        $this->decision = 'Validé';
        $this->validated_at = now();
        $this->comment = $comment ?? $this->comment;
        $this->save();

        // Log personnalisé d'approbation
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'report_id' => $this->monthly_report_id,
                'validator_id' => $this->validator_id,
                'decision' => 'approuvé',
                'comment' => $comment,
                'validated_at' => now()
            ])
            ->log('Rapport approuvé');

        // Mettre à jour le statut du rapport
        if ($this->report) {
            $this->report->status = 'approuvé';
            $this->report->save();
        }

        return $this;
    }

    /**
     * Rejeter la validation avec log personnalisé
     */
    public function reject(string $reason)
    {
        $this->decision = 'Rejeté';
        $this->validated_at = now();
        $this->comment = $reason;
        $this->save();

        // Log personnalisé de rejet
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'report_id' => $this->monthly_report_id,
                'validator_id' => $this->validator_id,
                'decision' => 'rejeté',
                'reason' => $reason,
                'validated_at' => now()
            ])
            ->log('Rapport rejeté');

        // Mettre à jour le statut du rapport
        // if ($this->report) {
        //     $this->report->status = 'rejeté';
        //     $this->report->save();
        // }

        return $this;
    }

    /**
     * Annuler la validation avec log personnalisé
     */
    public function cancel(string $reason = null)
    {
        $oldDecision = $this->decision;
        $this->decision = null;
        $this->validated_at = null;
        $this->save();

        // Log personnalisé d'annulation
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'report_id' => $this->monthly_report_id,
                'validator_id' => $this->validator_id,
                'old_decision' => $oldDecision,
                'reason' => $reason,
                'cancelled_at' => now()
            ])
            ->log('Validation de rapport annulée');

        // Mettre à jour le statut du rapport
        // if ($this->report) {
        //     $this->report->status = 'approuvé';
        //     $this->report->save();
        // }

        return $this;
    }

    /**
     * Ajouter un commentaire avec log
     */
    public function addComment(string $comment)
    {
        $oldComment = $this->comment;
        $this->comment = $comment;
        $this->save();

        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties([
                'report_id' => $this->monthly_report_id,
                'old_comment' => $oldComment,
                'new_comment' => $comment,
                'added_at' => now()
            ])
            ->log('Commentaire de validation ajouté');

        return $this;
    }

    /**
     * Récupère le nom du validateur
     */
    public function getValidatorNameAttribute(): string
    {
        return $this->validator?->name ?? 'Inconnu';
    }

    /**
     * Récupère la décision en texte lisible
     */
    public function getDecisionTextAttribute(): string
    {
        return match ($this->decision) {
            'approuvé' => 'Approuvé',
            'rejeté' => 'Rejeté',
            default => 'En attente',
        };
    }

    /**
     * Récupère la couleur associée à la décision
     */
    public function getDecisionColorAttribute(): string
    {
        return match ($this->decision) {
            'approuvé' => 'success',
            'rejeté' => 'danger',
            default => 'warning',
        };
    }
}
