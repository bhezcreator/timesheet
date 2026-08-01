<?php

namespace App\Livewire\Dashboard;

use App\Models\Activity;
use App\Models\MonthlyReport;
use App\Models\User;
use App\Services\OnlineUsersService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Personal extends Component
{
    protected OnlineUsersService $onlineUsersService;

    public $totalUsers = 0;

    public $todayActivities = 0;

    public $monthActivities = 0;

    public $totalHours = 0;

    public $submittedReports = 0;

    public $pendingReports = 0;

    public $user = null;

    public function mount()
    {
        $this->user = User::find(Auth::id());
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        // Récupérer l'utilisateur connecté
        $userId = $this->user->id;

        // Nombre total d'utilisateurs en ligne (simulé - vous pouvez adapter avec votre logique)
        // Vous pouvez utiliser une table de sessions ou un système de cache
        $this->totalUsers = $this->getOnlineUsers();

        // Activités du jour
        $this->todayActivities = Activity::where('user_id', $userId)
            ->whereDate('activity_date', Carbon::today())
            ->count();

        // Activités du mois
        $this->monthActivities = Activity::where('user_id', $userId)
            ->whereMonth('activity_date', Carbon::now()->month)
            ->whereYear('activity_date', Carbon::now()->year)
            ->count();

        // Nombre total d'heures (somme des durées des activités du mois)
        $this->totalHours = Activity::where('user_id', $userId)
            ->whereMonth('activity_date', Carbon::now()->month)
            ->whereYear('activity_date', Carbon::now()->year)
            ->sum('duration');

        // Rapports soumis (approuvés + soumis)
        $this->submittedReports = MonthlyReport::where('user_id', $userId)
            ->whereIn('status', ['approuvé', 'soumis'])
            ->count();

        // Rapports en attente (soumis uniquement)
        $this->pendingReports = MonthlyReport::where('user_id', $userId)
            ->where('status', 'soumis')
            ->count();
    }

    /**
     * Récupère le nombre d'utilisateurs en ligne
     * Adaptez cette méthode selon votre système d'authentification
     */
    public function boot(OnlineUsersService $onlineUsersService)
    {
        $this->onlineUsersService = $onlineUsersService;
    }

    private function getOnlineUsers(): int
    {
        if (! $this->user || $this->user->getRoleNames()->first() !== 'Admin') {
            return 0;
        }

        return $this->onlineUsersService->getOnlineUsersCount();
    }

    public function refresh()
    {
        $this->loadStatistics();
        $this->dispatch('statistics-updated');
    }

    public function render()
    {
        return view('livewire.dashboard.personal', [
            'user' => $this->user,
            'totalUsers' => $this->totalUsers,
            'todayActivities' => $this->todayActivities,
            'monthActivities' => $this->monthActivities,
            'totalHours' => $this->totalHours,
            'submittedReports' => $this->submittedReports,
            'pendingReports' => $this->pendingReports,
        ]);
    }
}
