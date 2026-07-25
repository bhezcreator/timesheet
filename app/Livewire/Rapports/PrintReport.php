<?php

namespace App\Livewire\Rapports;

use App\Models\MonthlyReport;
use App\Models\Activity;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('layouts.print')]
class PrintReport extends Component
{
    public $reportId;
    public $report;
    public $isMultiProject = false;

    public function mount($reportId)
    {
        $this->reportId = $reportId;

        // Chargement du rapport avec ses relations de base
        $this->report = MonthlyReport::with(['user', 'project'])
            ->findOrFail($this->reportId);

        // Détection : si project_ids est vide/null, c'est un rapport "Tous les projets" (Multi-projets)
        $this->isMultiProject = empty($this->report->project_ids);
    }

    /**
     * Récupère et structure les activités liées au rapport
     */
    public function getActivitiesProperty()
    {
        $query = Activity::where('user_id', $this->report->user_id)
            ->whereYear('activity_date', $this->report->year)
            ->whereMonth('activity_date', $this->report->month);

        // Si le rapport cible un projet unique, on applique le filtre strict
        if (!$this->isMultiProject) {
            $query->where('project_id', $this->report->project_ids);
        }

        return $query->with('project', 'activityType')
            ->orderBy('activity_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    public function render()
    {
        $activities = $this->activities;

        // Si mode multi-projets, on prépare la matrice pour le tableau croisé
        $matrix = [];
        $projectsList = [];

        if ($this->isMultiProject) {
            $projectsList = $activities->pluck('project.name', 'project_id')->unique()->filter();

            // Regroupement par jour puis par projet
            foreach ($activities as $activity) {
                $dayStr = $activity->activity_date->format('Y-m-d');
                $pId = $activity->project_id;

                if (!isset($matrix[$dayStr])) {
                    $matrix[$dayStr] = [
                        'date_formatted' => $activity->activity_date->translatedFormat('d F Y'),
                        'projects' => [],
                        'total_day' => 0
                    ];
                }

                if (!isset($matrix[$dayStr]['projects'][$pId])) {
                    $matrix[$dayStr]['projects'][$pId] = 0;
                }

                $matrix[$dayStr]['projects'][$pId] += (float) $activity->duration;
                $matrix[$dayStr]['total_day'] += (float) $activity->duration;
            }
        }

        return view('livewire.rapports.print-report', [
            'activities' => $activities,
            'matrix' => $matrix,
            'projectsList' => $projectsList,
            'totalReportHours' => $activities->sum('duration')
        ]); // Optionnel : utilise un layout épuré dédié à l'impression
    }
}
