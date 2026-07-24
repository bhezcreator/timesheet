<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MonthlyReport extends Model implements HasMedia
{
    use InteractsWithMedia;

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
     * Acesseur pour générer le titre dynamique du rapport
     * Utilisation dans Blade : {{ $rapport->full_title }}
     */
    // protected function fullTitle(): Attribute
    // {
    //     return Attribute::make(
    //         get: function () {
    //             // Traduction du mois en français (ex: Janvier)
    //             $monthName = Carbon::create()->month($this->month)->translatedFormat('F');

    //             // Si la colonne est vide ou null, le rapport concerne tous les projets
    //             if (empty($this->project_ids)) {
    //                 return "Rapport du mois de " . ucfirst($monthName) . " {$this->year} (pour tous les projets)";
    //             }

    //             // Si un projet est lié, on affiche son nom grâce au chargement de la relation
    //             $projectName = $this->project?->name ?? 'Projet Inconnu';
    //             return "Rapport du mois de " . ucfirst($monthName) . " {$this->year} concernant le projet : {$projectName}";
    //         }
    //     );
    // }

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
                // Sécurité Eloquent : si les données ne sont pas chargées, on quitte proprement
                if (!isset($this->attributes['month']) || !isset($this->attributes['year'])) {
                    return "Rapport d'activité";
                }

                $monthNum = (int) $this->attributes['month'];
                $yearNum = (int) $this->attributes['year'];

                // Traduction sécurisée du mois
                $monthName = \Carbon\Carbon::createFromDate($yearNum, $monthNum, 1)->translatedFormat('F');

                // Récupération sécurisée du contenu du champ project_ids
                $projectField = $this->attributes['project_ids'] ?? '';

                // Si c'est vide, null ou un tableau sérialisé vide, c'est pour tous les projets
                if (empty($projectField) || $projectField === '""' || $projectField === '[]') {
                    return "Rapport du mois de " . ucfirst($monthName) . " {$yearNum} (pour tous les projets)";
                }

                // Si un ID de projet est présent, on cherche son nom de manière sécurisée sans planter
                $projectName = \App\Models\Project::find($projectField)?->name ?? 'Projet Inconnu';

                return "Rapport du mois de " . ucfirst($monthName) . " {$yearNum} concernant le projet : {$projectName}";
            }
        );
    }
}
