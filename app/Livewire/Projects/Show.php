<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Activity;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Carbon\Carbon;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithPagination;
    
    public int $projectId;
    
    // Variables pour l'état du tableau de bord
    public array $stats = [];
    
    // Variables pour les activités
    public string $activityFilter = 'all';
    public string $searchActivity = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    
    // Variables pour le modal d'activité
    public bool $showActivityModal = false;
    public ?int $selectedActivityId = null;
    
    // Total des activités par projet
    public $totalActivities = 0;
    public $totalActivitiesSubmitted = 0;
    public $totalActivitiesApproved = 0;
    public $totalActivitiesRejected = 0;
    public $totalActivitiesDraft = 0;
    public $totalHours = 0;

    public $totalSubProjects = 0;
    public $completedActivities = 0;
    
    public ?int $moisChoisi = null;
    public ?int $anneeChoisie = null;

    /**
     * APRÈS (Correction validée)
     */
    public function mount(int $projectId)
    {
        $this->projectId = $projectId;
        $this->checkPermissionOrFail('projets.voir');
        $this->calculateProjectDashboardStats();
        $this->loadActivityStats();
        $this->anneeChoisie = Carbon::now()->year;
        $this->moisChoisi = now()->month;
    }

    public function updated($property): void
    {
        if (in_array($property, ['moisChoisi', 'anneeChoisie'])) {
            $this->loadActivityStats();
        }
    }
    
    /**
     * Statistiques des activités
    */
    private function loadActivityStats()
    {
        // 1. Requête de base : scope projet + filtres mois/année sur activity_date
        $base = Activity::query()->where('project_id', $this->projectId);

        $mois  = $this->moisChoisi  ?? now()->month;
        $annee = $this->anneeChoisie ?? now()->year;

        $base = Activity::query()
        ->where('project_id', $this->projectId)
        ->whereMonth('activity_date', $mois)
        ->whereYear('activity_date', $annee);

        // 2. Déclinaison par statut (clone pour ne pas polluer la requête de base)
        $this->totalActivities          = (clone $base)->count();
        $this->totalActivitiesSubmitted = (clone $base)->where('status', 'soumis')->count();
        $this->totalActivitiesApproved  = (clone $base)->where('status', 'approuvé')->count();
        $this->totalActivitiesRejected  = (clone $base)->where('status', 'rejeté')->count();
        $this->totalActivitiesDraft     = (clone $base)->where('status', 'brouillon')->count();

        // 3. Somme des heures : uniquement les activités approuvées
        $this->totalHours = (clone $base)
            ->where('status', 'approuvé')
            ->sum('duration') ?? 0;
    }

    public function getAnneesProperty(): array
    {
        $current = Carbon::now()->year;

        // Ex : de l'année courante - 5 à l'année courante + 1
        return collect(range($current - 5, $current + 1))
            ->mapWithKeys(fn (int $y) => [
                $y => (string) $y,
            ])
            ->all();
    }

        /**
     * Remplir automatique le champ Mois
     */
    public function getMoisProperty(): array
    {
        return collect(range(1, 12))
            ->mapWithKeys(fn (int $m) => [
                $m => ucfirst(Carbon::create(null, $m, 1)->locale('fr')->translatedFormat('F')),
            ])
            ->all();
    }

    /**
     * Calcule toutes les statistiques du mini-dashboard de manière optimisée.
     */
    public function calculateProjectDashboardStats()
    {
        $project = Project::query()
            ->with([
                'manager',
                'users',
                'subProjects' => function ($query) {
                    $query->withCount('activities');
                },
                'activities',
            ])
            ->withCount(['subProjects', 'activities', 'users'])
            ->findOrFail($this->projectId);
        
        // 1. Calcul de l'avancement global
        $this->totalSubProjects = $project->sub_projects_count;
        $completedSubProjects = $project->subProjects->where('status', 'approuvé')->count();
        
        $totalActivities = $project->activities_count;
        
        $this->completedActivities = $project->activities->where('status', 'approuvé')->count();

        // Calcul du pourcentage pondéré
        $subProjectProgress = $this->totalSubProjects > 0 ? ($completedSubProjects / $this->totalSubProjects) * 100 : 0;
        $activityProgress = $totalActivities > 0 ? ($this->completedActivities / $totalActivities) * 100 : 0;
        
        $globalProgress = 0;
        if ($this->totalSubProjects > 0 && $totalActivities > 0) {
            $globalProgress = round(($subProjectProgress + $activityProgress) / 2);
        } elseif ($this->totalSubProjects > 0) {
            $globalProgress = round($subProjectProgress);
        } elseif ($totalActivities > 0) {
            $globalProgress = round($activityProgress);
        }
        
        // 2. Formatage des données pour la vue
        $this->stats = [
            'project' => $project,
            'global_progress' => $globalProgress,
            'sub_projects_completed' => $completedSubProjects,
            'totalActivit' => $totalActivities,
            'days_remaining' => $project->end_date ? now()->diffInDays($project->end_date, false) : null,
        ];
    }
    
    /**
     * Récupère la liste des activités du projet avec filtres
     */
    public function getActivitiesProperty()
    {
        $query = Activity::query()
            ->with(['user', 'subProject'])
            ->where('project_id', $this->projectId);
        
        // Filtre par statut
        if ($this->activityFilter === 'approuvé') {
            $query->where('status', 'approuvé');
        } elseif ($this->activityFilter === 'brouillon') {
            $query->where('status', 'brouillon');
        } elseif ($this->activityFilter === 'soumis') {
            $query->where('status', 'soumis');
        } elseif ($this->activityFilter === 'rejeté') {
            $query->where('status', 'rejeté');
        }
        
        // Recherche
        if (!empty($this->searchActivity)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchActivity . '%')
                  ->orWhere('description', 'like', '%' . $this->searchActivity . '%');
            });
        }
        
        // Tri
        $query->orderBy($this->sortBy, $this->sortDirection);
        
        return $query->paginate($this->perPage);
    }
    
    /**
     * Récupère les statistiques des activités
     */
    public function getActivityStatsProperty()
    {
        return Activity::where('project_id', $this->projectId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "approuvé" THEN 1 ELSE 0 END) as approuve,
                SUM(CASE WHEN status = "soumis" THEN 1 ELSE 0 END) as soumis,
                SUM(CASE WHEN status = "brouillon" THEN 1 ELSE 0 END) as brouillon,
                SUM(CASE WHEN status = "rejeté" THEN 1 ELSE 0 END) as rejete
            ')
            ->first();
    }
    
    /**
     * Réinitialiser les filtres
     */
    public function resetFilters()
    {
        $this->activityFilter = 'all';
        $this->searchActivity = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }
    
    /**
     * Changer le tri
     */
    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    /**
     * Sécurité standard de votre application.
     */
    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }
        
        throw ValidationException::withMessages([
            'permission' => ['Action non autorisée : Privilèges insuffisants pour consulter ce projet.'],
        ]);
    }
    
    public function render()
    {
        return view('livewire.projects.show', [
            ...$this->stats,
            'activities' => $this->activities,
            'activityStats' => $this->activity_stats,
        ]);
    }
}