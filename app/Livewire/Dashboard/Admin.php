<?php

namespace App\Livewire\Dashboard;

use App\Models\Activity;
use App\Models\MonthlyReport;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Admin extends Component
{
    public $user;
    public $hasPermission = true;
    public $errorMessage = '';

    // Statistiques globales
    public $totalProjects = 0;
    public $activeProjects = 0;
    public $completedProjects = 0;
    public $totalSubProjects = 0;
    public $activeSubProjects = 0;
    public $totalUsers = 0;
    public $totalActivities = 0;
    public $totalActivitiesMonth = 0;
    public $totalHours = 0;
    public $totalReports = 0;
    public $pendingReports = 0;
    public $validationRate = 0;

    // Données pour les graphiques
    public $projectsByStatus = [];
    public $activitiesByMonth = [];
    public $reportsByStatus = [];
    public $topProjects = [];
    public $recentProjects = [];
    public $projectStats = [];

    public function mount()
    {
        $this->user = User::find(Auth::id());

        try {
            // Vérifier si l'utilisateur a la permission tableauAdmin
            if (!$this->user || !$this->user->can('tableauAdmin')) {
                $this->hasPermission = false;
                $this->errorMessage = 'Vous n\'avez pas les permissions nécessaires pour accéder à ce tableau de bord.';
                return;
            }

            $this->loadStatistics();
        } catch (\Exception $e) {
            $this->hasPermission = false;
            $this->errorMessage = 'Une erreur est survenue lors du chargement des données.';
            Log::error('Erreur dans Admin Dashboard', [
                'error' => $e->getMessage(),
                'user_id' => $this->user?->id
            ]);
        }
    }

    /**
     * Charge toutes les statistiques
     */
    private function loadStatistics()
    {
        $this->loadProjectStats();
        $this->loadSubProjectStats();
        $this->loadUserStats();
        $this->loadActivityStats();
        $this->loadReportStats();
        $this->loadChartData();
        $this->loadTopProjects();
        $this->loadRecentProjects();
    }

    // Méthode qui sera appelée lorsque l'événement est déclenché : statistics-updated
    public function onStatisticsUpdated()
    {
        $this->loadStatistics();
    }

    /**
     * Statistiques des projets
     */
    private function loadProjectStats()
    {
        $this->totalProjects = Project::count();
        $this->activeProjects = Project::where('status', 'actif')->count();
        $this->completedProjects = Project::where('status', 'terminé')->count();

        // Projets par statut
        $this->projectsByStatus = Project::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    /**
     * Statistiques des sous-projets
     */
    private function loadSubProjectStats()
    {
        $this->totalSubProjects = SubProject::count();
        $this->activeSubProjects = SubProject::where('status', 'actif')->count();
    }

    /**
     * Statistiques des utilisateurs
     */
    private function loadUserStats()
    {
        $this->totalUsers = User::count();
    }

    /**
     * Statistiques des activités
     */
    private function loadActivityStats()
    {
        $this->totalActivities = Activity::count();
        $this->totalActivitiesMonth = Activity::whereMonth('activity_date', Carbon::now()->month)
            ->whereYear('activity_date', Carbon::now()->year)
            ->count();
        $this->totalHours = Activity::sum('duration');

        // Activités par mois (pour le graphique)
        $this->activitiesByMonth = Activity::select(
            DB::raw('MONTH(activity_date) as month'),
            DB::raw('YEAR(activity_date) as year'),
            DB::raw('count(*) as total')
        )
            ->whereYear('activity_date', Carbon::now()->year)
            ->groupBy(DB::raw('MONTH(activity_date)'), DB::raw('YEAR(activity_date)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'month' => $date->translatedFormat('F'), // Déjà formaté
                    'month_number' => $item->month,
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }

    /**
     * Statistiques des rapports
     */
    private function loadReportStats()
    {
        $this->totalReports = MonthlyReport::count();
        $this->pendingReports = MonthlyReport::where('status', 'soumis')->count();

        // Taux de validation
        $validated = MonthlyReport::whereIn('status', ['approuvé', 'rejeté'])->count();
        $approved = MonthlyReport::where('status', 'approuvé')->count();
        $this->validationRate = $validated > 0 ? round(($approved / $validated) * 100, 1) : 0;

        // Rapports par statut
        $this->reportsByStatus = MonthlyReport::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    /**
     * Données pour les graphiques
     */
    private function loadChartData()
    {
        // Statistiques par projet
        $this->projectStats = Project::withCount(['subProjects', 'activities'])
            ->withSum('activities', 'duration')
            ->limit(10)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'sub_projects_count' => $project->sub_projects_count,
                    'activities_count' => $project->activities_count,
                    'total_hours' => number_format($project->activities_sum_duration ?? 0, 1),
                    'status' => $project->status,
                ];
            })
            ->toArray();
    }

    /**
     * Top projets les plus actifs
     */
    private function loadTopProjects()
    {
        $this->topProjects = Project::withCount(['activities'])
            ->withSum('activities', 'duration')
            ->orderBy('activities_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'code' => $project->code,
                    'activities_count' => $project->activities_count,
                    'total_hours' => number_format($project->activities_sum_duration ?? 0, 1),
                    'status' => $project->status,
                ];
            })
            ->toArray();
    }

    /**
     * Projets récents
     */
    private function loadRecentProjects()
    {
        $this->recentProjects = Project::with(['manager', 'subProjects'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'code' => $project->code,
                    'status' => $project->status,
                    'manager_name' => $project->manager?->name ?? 'Non assigné',
                    'sub_projects_count' => $project->subProjects->count(),
                    'start_date' => $project->start_date?->format('d/m/Y'),
                    'end_date' => $project->end_date?->format('d/m/Y'),
                    'created_at' => $project->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.admin', [
            'user' => $this->user,
        ]);
    }
}
