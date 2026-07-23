<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyReport extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validation()
    {
        return $this->hasOne(ReportValidation::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Relation optionnelle avec le Projet si un ID est spécifié
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_ids');
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
    protected function fullTitle(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Traduction du mois en français (ex: Janvier)
                $monthName = \Carbon\Carbon::create()->month($this->month)->translatedFormat('F');

                // Si la colonne est vide ou null, le rapport concerne tous les projets
                if (empty($this->project_ids)) {
                    return "Rapport du mois de " . ucfirst($monthName) . " {$this->year} (Tous les projets)";
                }

                // Si un projet est lié, on affiche son nom grâce au chargement de la relation
                $projectName = $this->project?->name ?? 'Projet Inconnu';
                return "Rapport du mois de " . ucfirst($monthName) . " {$this->year} concernant le projet : {$projectName}";
            }
        );
    }
}
