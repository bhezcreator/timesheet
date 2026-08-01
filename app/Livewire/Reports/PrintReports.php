<?php

namespace App\Livewire\Reports;

use App\Models\MonthlyReport;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.print')]
class PrintReports extends Component
{
    public $reports;

    public $filters = [];

    public $statistics = [];

    public $status = '';

    public $month = '';

    public $year = '';

    public $search = '';

    public function mount($filters = null)
    {
        $this->loadFilters($filters);
        $this->loadReports();
    }

    public function loadFilters($filters)
    {
        // Si des filtres sont passés en paramètre
        if ($filters) {
            if (is_string($filters)) {
                parse_str($filters, $this->filters);
            } elseif (is_array($filters)) {
                $this->filters = $filters;
            }
        } else {
            // Valeurs par défaut
            $this->filters = [
                'status' => '',
                'month' => now()->format('m'),
                'year' => now()->format('Y'),
                'search' => '',
            ];
        }

        // Assigner les valeurs aux propriétés
        $this->status = $this->filters['status'] ?? '';
        $this->month = $this->filters['month'] ?? now()->format('m');
        $this->year = $this->filters['year'] ?? now()->format('Y');
        $this->search = $this->filters['search'] ?? '';
    }

    public function loadReports()
    {
        try {
            $query = MonthlyReport::with(['user', 'activities']);

            // Appliquer les filtres
            if (! empty($this->status)) {
                $query->where('status', $this->status);
            }

            if (! empty($this->month) && ! empty($this->year)) {
                $query->where('month', $this->month)
                    ->where('year', $this->year);
            }

            if (! empty($this->search)) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                });
            }

            $query->where('status', '!=', 'brouillon');

            $this->reports = $query->orderBy('created_at', 'desc')->get();

            // Calculer les statistiques
            $this->statistics = [
                'total' => $this->reports->count(),
                'soumis' => $this->reports->where('status', 'soumis')->count(),
                'approuves' => $this->reports->where('status', 'approuvé')->count(),
                'rejetes' => $this->reports->where('status', 'rejeté')->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des rapports pour impression', [
                'error' => $e->getMessage(),
                'filters' => $this->filters,
            ]);

            $this->reports = collect([]);
            $this->statistics = [
                'total' => 0,
                'soumis' => 0,
                'approuves' => 0,
                'rejetes' => 0,
            ];
        }
    }

    /**
     * Normalise les project_ids en tableau
     */
    private function normalizeProjectIds($ids): array
    {
        if (empty($ids)) {
            return [];
        }

        if (is_array($ids)) {
            return array_filter($ids);
        }

        if (is_string($ids)) {
            // Essayer de décoder JSON
            if (str_starts_with($ids, '[')) {
                $decoded = json_decode($ids, true);
                if (is_array($decoded)) {
                    return array_filter($decoded);
                }
            }

            // Si c'est un nombre entre guillemets
            if (str_starts_with($ids, '"')) {
                $decoded = json_decode($ids);
                if (is_numeric($decoded)) {
                    return [(int) $decoded];
                }
            }

            // Si c'est une chaîne avec des virgules
            if (str_contains($ids, ',')) {
                $parts = explode(',', $ids);

                return array_filter(array_map('trim', $parts));
            }

            // Si c'est un nombre simple
            if (is_numeric($ids)) {
                return [(int) $ids];
            }
        }

        return [];
    }

    public function render()
    {
        return view('livewire.reports.print-reports', [
            'reports' => $this->reports,
            'filters' => $this->filters,
            'statistics' => $this->statistics,
        ]);
    }
}
