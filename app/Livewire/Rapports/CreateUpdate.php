<?php

namespace App\Livewire\Rapports;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

use App\Models\MonthlyReport;
use App\Models\Activity;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateUpdate extends Component
{
    // ID du rapport (null si création)
    public $ID_report = null;

    // Propriétés du formulaire
    public $month;
    public $year;
    public $report_date;
    public $objectives;
    public $achievements;
    public $next_actions;
    public $status = 'draft';

    // Filtrage des activités
    public $selected_project_id = 'all';

    // Données de configuration (Settings)
    public $reportFrequency = 'month'; // 'month' ou 'week'
    public $calculateOvertime = false;
    public $standardHoursPerMonth = 160;

    // Données calculées pour l'affichage
    public $totalHours = 0;
    public $overtimeHours = 0;

    public int $user_id;

    protected function rules()
    {
        // 1. Récupérer les identifiants des projets assignés à l'utilisateur
        $assignedProjectIds = \App\Models\User::find($this->user_id)
            ?->projects()
            ->pluck('projects.id')
            ->toArray() ?? [];

        // 2. Autoriser la valeur 'all' en plus des IDs de projets assignés
        $allowedProjectValues = array_merge(['all'], $assignedProjectIds);

        return [
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'report_date' => 'required|date',
            'objectives' => 'required|string|min:10',
            'achievements' => 'required|string|min:10',
            'next_actions' => 'required|string|min:10',
            'selected_project_id' => [
                'required',
                \Illuminate\Validation\Rule::in($allowedProjectValues)
            ],
        ];
    }

    public function mount($reportId = null)
    {
        $this->checkPermissionOrFail("rapports.voir");

        $this->ID_report = $reportId ? (int) $reportId : null;
        $this->user_id = Auth::id();

        // Charger les configurations globales
        $this->loadSettings();

        if ($this->ID_report) {
            // Mode Édition
            $report = MonthlyReport::where('user_id', $this->user_id)->findOrFail($this->ID_report);
            $this->month = $report->month;
            $this->year = $report->year;
            $this->report_date = $report->report_date->format('Y-m-d');
            $this->objectives = $report->objectives;
            $this->achievements = $report->achievements;
            $this->next_actions = $report->next_actions;
            $this->status = $report->status;
        } else {
            // Mode Création (Valeurs par défaut)
            $this->month = now()->month;
            $this->year = now()->year;
            $this->report_date = now()->format('Y-m-d');
        }
    }

    private function loadSettings()
    {
        $settings = Setting::whereIn('key', ['report_frequency', 'calculate_overtime', 'standard_hours_per_month'])
            ->pluck('value', 'key');

        $this->reportFrequency = $settings->get('report_frequency', 'month');
        $this->calculateOvertime = filter_var($settings->get('calculate_overtime', false), FILTER_VALIDATE_BOOLEAN);
        $this->standardHoursPerMonth = (int) $settings->get('standard_hours_per_month', 160);
    }

    // Récupérer les activités selon la période et le projet sélectionné
    public function getActivitiesProperty()
    {
        // 1. Récupérer les identifiants des projets assignés à l'utilisateur
        $assignedProjectIds = User::find($this->user_id)
            ?->projects()
            ->pluck('projects.id') // Remplacez 'projects.id' par le nom correct si nécessaire
            ->toArray() ?? [];

        // 2. Construire la requête de base pour les activités de l'utilisateur
        $query = Activity::where('user_id', $this->user_id)
            ->whereYear('activity_date', $this->year)
            ->whereMonth('activity_date', $this->month);

        // 3. Appliquer le filtre de projet sélectionné tout en respectant ses affectations
        if ($this->selected_project_id !== 'all') {
            // Si un projet spécifique est choisi, on vérifie s'il fait partie de ses projets assignés
            if (in_array($this->selected_project_id, $assignedProjectIds)) {
                $query->where('project_id', $this->selected_project_id);
            } else {
                // Sécurité : Si le projet n'est pas assigné, on force une liste vide
                $query->whereRaw('1 = 0');
            }
        } else {
            // Si "Tous les projets" est sélectionné, on restreint uniquement à ses projets assignés
            $query->whereIn('project_id', $assignedProjectIds);
        }

        $activities = $query->with('project', 'activityType')->orderBy('activity_date', 'asc')->get();

        // Calcul des totaux d'heures
        $this->totalHours = $activities->sum('duration');
        if ($this->calculateOvertime && $this->totalHours > $this->standardHoursPerMonth) {
            $this->overtimeHours = $this->totalHours - $this->standardHoursPerMonth;
        } else {
            $this->overtimeHours = 0;
        }

        return $activities;
    }


    public function save($submit = false)
    {
        if ($submit) {
            $this->checkPermissionOrFail("rapports.modifier");
        } else {
            $this->checkPermissionOrFail("rapports.creer");
        }

        $this->validate();

        $data = [
            'user_id' => $this->user_id,
            'month' => $this->month,
            'year' => $this->year,
            'report_date' => $this->report_date,
            'objectives' => $this->objectives,
            'achievements' => $this->achievements,
            'next_actions' => $this->next_actions,
            'status' => $submit ? 'soumis' : 'brouillon',
            'submitted_at' => $submit ? now() : null,
        ];

        // Sauvegarde ou Mise à jour du rapport
        $report = MonthlyReport::updateOrCreate(
            ['id' => $this->ID_report, 'user_id' => $this->user_id],
            $data
        );

        // Lier toutes les activités affichées à ce rapport
        Activity::whereIn('id', $this->activities->pluck('id'))->update([
            'monthly_report_id' => $report->id,
            'status' => $submit ? 'soumis' : 'brouillon',
        ]);

        session()->flash('message', $submit ? 'Rapport soumis avec succès !' : 'Brouillon enregistré avec succès !');

        return redirect()->route('rapports.index'); // Modifiez selon votre route réelle
    }

    public function render()
    {
        $user = User::find($this->user_id);
        return view('livewire.rapports.create-update', [
            'projects' => $user->projects()->get(),
            'activitiesList' => $this->activities,
        ]);
    }

    /**
     * Valide une permission et lève une erreur propre interceptée par le Front-End.
     */
    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ["Action non autorisée : Privilèges insuffisants pour exécuter cette opération."]
        ]);
    }
}
