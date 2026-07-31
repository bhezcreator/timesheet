<?php

namespace App\Livewire\Dashboard;

use App\Models\Activity;
use App\Models\MonthlyReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Supervisor extends Component
{
    public $user;
    public $teamMembers = [];
    public $pendingReports = 0;
    public $totalReports = 0;
    public $validationRate = 0;
    public $teamStats = [
        'total_activities' => 0,
        'total_hours' => 0,
        'submitted_reports' => 0,
        'approved_reports' => 0,
        'rejected_reports' => 0,
        'pending_reports' => 0,
    ];
    public $recentActivities = [];
    public $reportsByStatus = [];
    public $topPerformers = [];

    public function mount()
    {
        $this->user = User::find(Auth::id());

        // Vérifier les permissions
        if (!$this->user->can('validations.voir')) {
            throw ValidationException::withMessages([
                'permission' => ["Action non autorisée : Vous n'avez pas les permissions nécessaires.."]
            ]);
        }

        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        // Récupérer les membres de l'équipe du superviseur
        $this->loadTeamMembers();

        // Charger les statistiques
        $this->loadPendingReports();
        $this->loadTeamStats();
        $this->loadValidationRate();
        $this->loadRecentActivities();
        $this->loadReportsByStatus();
        $this->loadTopPerformers();
    }

    /**
     * Charge les membres de l'équipe du superviseur
     */
    private function loadTeamMembers()
    {
        // Récupérer les utilisateurs supervisés
        // Adaptez selon votre logique (ex: via une relation ou un champ supervisor_id)
        $this->teamMembers = User::where('supervisor_id', $this->user->id)
            ->orWhereHas('roles', function ($query) {
                $query->where('name', '!=', 'Superviseur')
                    ->where('name', '!=', 'Admin');
            })
            ->where('id', '!=', $this->user->id)
            ->limit(5)
            ->get(['id', 'name', 'email'])
            ->toArray();

        // Si aucun membre d'équipe, on prend quelques utilisateurs pour la démo
        if (empty($this->teamMembers)) {
            $this->teamMembers = User::where('id', '!=', $this->user->id)
                ->limit(5)
                ->get(['id', 'name', 'email'])
                ->toArray();
        }
    }

    /**
     * Charge les rapports en attente de validation
     */
    private function loadPendingReports()
    {
        // Récupérer les rapports soumis par l'équipe du superviseur
        $teamIds = array_column($this->teamMembers, 'id');

        $this->pendingReports = MonthlyReport::where('status', 'soumis')
            ->when(!empty($teamIds), function ($query) use ($teamIds) {
                return $query->whereIn('user_id', $teamIds);
            })
            ->count();

        // Total des rapports soumis
        $this->totalReports = MonthlyReport::whereIn('status', ['soumis', 'approuvé', 'rejeté'])
            ->when(!empty($teamIds), function ($query) use ($teamIds) {
                return $query->whereIn('user_id', $teamIds);
            })
            ->count();
    }

    /**
     * Charge les statistiques de l'équipe
     */
    private function loadTeamStats()
    {
        $teamIds = array_column($this->teamMembers, 'id');

        if (empty($teamIds)) {
            return;
        }

        // Activités du mois
        $this->teamStats['total_activities'] = Activity::whereIn('user_id', $teamIds)
            ->whereMonth('activity_date', Carbon::now()->month)
            ->whereYear('activity_date', Carbon::now()->year)
            ->count();

        // Total des heures du mois
        $this->teamStats['total_hours'] = Activity::whereIn('user_id', $teamIds)
            ->whereMonth('activity_date', Carbon::now()->month)
            ->whereYear('activity_date', Carbon::now()->year)
            ->sum('duration');

        // Rapports par statut
        $this->teamStats['submitted_reports'] = MonthlyReport::whereIn('user_id', $teamIds)
            ->where('status', 'soumis')
            ->count();

        $this->teamStats['approved_reports'] = MonthlyReport::whereIn('user_id', $teamIds)
            ->where('status', 'approuvé')
            ->count();

        $this->teamStats['rejected_reports'] = MonthlyReport::whereIn('user_id', $teamIds)
            ->where('status', 'rejeté')
            ->count();

        $this->teamStats['pending_reports'] = $this->pendingReports;
    }

    /**
     * Calcule le taux de validation
     */
    private function loadValidationRate()
    {
        $teamIds = array_column($this->teamMembers, 'id');

        if (empty($teamIds)) {
            $this->validationRate = 0;
            return;
        }

        $total = MonthlyReport::whereIn('user_id', $teamIds)
            ->whereIn('status', ['approuvé', 'rejeté'])
            ->count();

        $approved = MonthlyReport::whereIn('user_id', $teamIds)
            ->where('status', 'approuvé')
            ->count();

        $this->validationRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;
    }

    /**
     * Charge les activités récentes de l'équipe
     */
    private function loadRecentActivities()
    {
        $teamIds = array_column($this->teamMembers, 'id');

        if (empty($teamIds)) {
            $this->recentActivities = [];
            return;
        }

        $this->recentActivities = Activity::with(['user', 'project', 'activityType'])
            ->whereIn('user_id', $teamIds)
            ->where('status', 'soumis')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'titre' => $activity->titre,
                    'user_name' => $activity->user->name ?? 'N/A',
                    'project_name' => $activity->project->name ?? 'N/A',
                    'activity_type' => $activity->activityType->name ?? 'N/A',
                    'duration' => $activity->duration,
                    'created_at' => $activity->created_at->diffForHumans(),
                    'status' => $activity->status,
                ];
            })
            ->toArray();
    }

    /**
     * Charge les rapports par statut pour les graphiques
     */
    private function loadReportsByStatus()
    {
        $teamIds = array_column($this->teamMembers, 'id');

        if (empty($teamIds)) {
            $this->reportsByStatus = [
                'brouillon' => 0,
                'soumis' => 0,
                'approuvé' => 0,
                'rejeté' => 0,
            ];
            return;
        }

        $stats = MonthlyReport::whereIn('user_id', $teamIds)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $this->reportsByStatus = [
            'brouillon' => $stats['brouillon'] ?? 0,
            'soumis' => $stats['soumis'] ?? 0,
            'approuvé' => $stats['approuvé'] ?? 0,
            'rejeté' => $stats['rejeté'] ?? 0,
        ];
    }

    /**
     * Charge les meilleurs performers de l'équipe
     */
    private function loadTopPerformers()
    {
        $teamIds = array_column($this->teamMembers, 'id');

        if (empty($teamIds)) {
            $this->topPerformers = [];
            return;
        }

        $this->topPerformers = User::whereIn('id', $teamIds)
            ->withCount(['activities' => function ($query) {
                $query->whereMonth('activity_date', Carbon::now()->month)
                    ->whereYear('activity_date', Carbon::now()->year);
            }])
            ->withSum(['activities' => function ($query) {
                $query->whereMonth('activity_date', Carbon::now()->month)
                    ->whereYear('activity_date', Carbon::now()->year);
            }], 'duration')
            ->orderBy('activities_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'activities_count' => $user->activities_count,
                    'total_hours' => number_format($user->activities_sum_duration ?? 0, 1),
                ];
            })
            ->toArray();
    }

    // Méthode qui sera appelée lorsque l'événement est déclenché : statistics-updated
    public function onStatisticsUpdated()
    {
        $this->loadStatistics();
    }

    /**
     * Approuve un rapport
     */
    public function approveReport($reportId)
    {
        $report = MonthlyReport::findOrFail($reportId);

        // Vérifier que le rapport appartient bien à un membre de l'équipe
        $teamIds = array_column($this->teamMembers, 'id');
        if (!in_array($report->user_id, $teamIds)) {
            session()->flash('error', 'Vous n\'êtes pas autorisé à valider ce rapport.');
            return;
        }

        $report->update([
            'status' => 'approuvé',
            'submitted_at' => now(),
        ]);

        session()->flash('success', 'Rapport approuvé avec succès.');
        $this->loadStatistics();
        $this->dispatch('statistics-updated');
    }

    /**
     * Rejette un rapport
     */
    public function rejectReport($reportId, $reason = null)
    {
        $report = MonthlyReport::findOrFail($reportId);

        // Vérifier que le rapport appartient bien à un membre de l'équipe
        $teamIds = array_column($this->teamMembers, 'id');
        if (!in_array($report->user_id, $teamIds)) {
            session()->flash('error', 'Vous n\'êtes pas autorisé à valider ce rapport.');
            return;
        }

        $report->update([
            'status' => 'rejeté',
            'rejection_reason' => $reason ?? 'Rapport rejeté par le superviseur.',
            'submitted_at' => now(),
        ]);

        session()->flash('success', 'Rapport rejeté.');
        $this->loadStatistics();
        $this->dispatch('statistics-updated');
    }

    /**
     * Voir les détails d'un rapport
     */
    public function viewReport($reportId)
    {
        // Rediriger vers la page de détail du rapport
        return redirect()->route('reports.show', $reportId);
    }

    public function render()
    {
        return view('livewire.dashboard.supervisor', [
            'user' => $this->user,
            'teamMembers' => $this->teamMembers,
            'pendingReports' => $this->pendingReports,
            'totalReports' => $this->totalReports,
            'validationRate' => $this->validationRate,
            'teamStats' => $this->teamStats,
            'recentActivities' => $this->recentActivities,
            'reportsByStatus' => $this->reportsByStatus,
            'topPerformers' => $this->topPerformers,
        ]);
    }
}
