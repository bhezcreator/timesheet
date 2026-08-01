<?php

namespace App\Livewire\Reports;

use App\Models\MonthlyReport;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ReportList extends Component
{
    use WithPagination;

    public $status = '';

    public $month = '';

    public $year = '';

    public $search = '';

    public $perPage = 15;

    public $userFilter = '';

    // Statistiques
    public $totalSoumis = 0;

    public $totalApprouves = 0;

    public $totalRejetes = 0;

    public $totalBrouillons = 0;

    // Pour l'impression
    public $isPrinting = false;

    protected $queryString = [
        'status' => ['except' => ''],
        'month' => ['except' => ''],
        'year' => ['except' => ''],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->month = now()->format('m');
        $this->year = now()->format('Y');
        $this->loadStatistics();
    }

    public function updatedStatus()
    {
        $this->resetPage();
        $this->loadStatistics();
    }

    public function updatedMonth()
    {
        $this->resetPage();
        $this->loadStatistics();
    }

    public function updatedYear()
    {
        $this->resetPage();
        $this->loadStatistics();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function loadStatistics()
    {
        $query = MonthlyReport::query();
        $query->where('status', '!=', 'brouillon');
        // Filtrer par mois/année
        if ($this->month && $this->year) {
            $query->where('month', $this->month)
                ->where('year', $this->year);
        }

        $this->totalSoumis = (clone $query)->where('status', 'soumis')->count();
        $this->totalApprouves = (clone $query)->where('status', 'approuvé')->count();
        $this->totalRejetes = (clone $query)->where('status', 'rejeté')->count();
    }

    public function getReports()
    {
        $query = MonthlyReport::with(['user', 'activities']);

        // Filtre par statut
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // Filtre par mois/année
        if ($this->month && $this->year) {
            $query->where('month', $this->month)
                ->where('year', $this->year);
        }

        // Recherche par nom d'utilisateur ou titre
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($subQuery) {
                    $subQuery->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                })->orWhere('id', 'like', '%'.$this->search.'%');
            });
        }

        // Filtrer par utilisateur si spécifié
        if ($this->userFilter) {
            $query->where('user_id', $this->userFilter);
        }

        $query->where('status', '!=', 'brouillon');

        return $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getUsers()
    {
        return User::orderBy('name')->get(['id', 'name', 'first_name', 'last_name']);
    }

    public function printReports()
    {
        // Construire l'URL avec les paramètres de filtre
        $params = http_build_query([
            'status' => $this->status,
            'month' => $this->month,
            'year' => $this->year,
            'search' => $this->search,
        ]);

        // Rediriger vers la page d'impression
        return redirect()->route('reports.print', ['filters' => $params]);
    }

    public function refreshData()
    {
        $this->loadStatistics();
        $this->dispatch('statistics-updated');
    }

    public function resetFilters()
    {
        $this->status = '';
        $this->search = '';
        $this->month = now()->format('m');
        $this->year = now()->format('Y');
        $this->resetPage();
        $this->loadStatistics();
    }

    public function render()
    {
        $reports = $this->getReports();
        $users = $this->getUsers();

        return view('livewire.reports.report-list', [
            'reports' => $reports,
            'users' => $users,
        ]);
    }
}
