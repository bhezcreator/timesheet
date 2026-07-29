<?php

namespace App\Livewire\Rapports;

use App\Models\Activity;
use App\Models\MonthlyReport;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Events\UniversalModelStatusChanged;

#[Layout('layouts.app')]
class CreateUpdate extends Component
{
    use WithFileUploads;
    // ID du rapport (null si création)
    public $ID_report = null;

    // Propriétés du formulaire
    public $month;
    public $year;
    public $report_date;
    public $objectives;
    public $achievements;
    public $next_actions;
    public $status = 'brouillon';

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

    // Propriétés pour la gestion des fichiers
    public $files = []; // Stocke les nouveaux fichiers téléversés
    public $existingFiles = []; // Liste des fichiers déjà en BDD (Mode Édition)

    protected function rules()
    {
        // 1. Récupérer les identifiants des projets assignés à l'utilisateur
        $assignedProjectIds = User::find($this->user_id)
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
                Rule::in($allowedProjectValues),
                'files.*' => [
                    'nullable',
                    'file',
                    'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp',
                    'max:5120' // 5 Mo maximum par fichier
                ],
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

            // Charger les pièces jointes existantes
            $this->loadAttachments($report);
        } else {
            // Mode Création (Valeurs par défaut)
            $this->month = now()->month;
            $this->year = now()->year;
            $this->report_date = now()->format('Y-m-d');
        }
    }

    private function loadAttachments(MonthlyReport $report)
    {
        $this->existingFiles = $report->getMedia('attachments')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->file_name,
                'size' => $media->human_readable_size,
                'url' => $media->getUrl()
            ];
        })->toArray();
    }

    // Supprimer une pièce jointe existante
    public function deleteAttachment($mediaId)
    {
        if ($this->ID_report) {
            $report = MonthlyReport::where('user_id', $this->user_id)->findOrFail($this->ID_report);
            $media = $report->media()->find($mediaId);
            if ($media) {
                $media->delete();
                $this->loadAttachments($report);
            }
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
        try {
            if ($this->ID_report) {
                $this->checkPermissionOrFail("rapports.modifier");
            } else {
                $this->checkPermissionOrFail("rapports.creer");
            }

            $selected_project_id = ($this->selected_project_id === 'all') ? '' : $this->selected_project_id;
            $this->validate();

            // 🔍 VÉRIFICATION DE LA CONTRAINTE D'UNICITÉ
            if (!$this->ID_report) {
                $this->checkUniqueReportConstraint();
            }

            $data = [
                'user_id' => $this->user_id,
                'month' => $this->month,
                'year' => $this->year,
                'project_ids' => $selected_project_id,
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

            // Traitement des pièces jointes Spatie Media Library
            if (!empty($this->files)) {
                foreach ($this->files as $file) {
                    $report->addMedia($file->getRealPath())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('attachments');
                }
                $this->reset('files');
            }

            // Lier toutes les activités affichées à ce rapport
            Activity::whereIn('id', $this->activities->pluck('id'))->update([
                'monthly_report_id' => $report->id,
                'status' => $submit ? 'soumis' : 'brouillon',
            ]);

            if ($submit) {
                $supervise = $report->user->supervisor;

                event(new UniversalModelStatusChanged(
                    model: $report,
                    recipient: $supervise,
                    title: "Soumission du : " . $report->full_title,
                    messageContent: "Le colaborateur " . $report->user->name . ' ' . $report->user->first_name . " vient de soumetre son rapport.",
                    status: 'soumis',
                    comment: '',
                    routeUrl: route('validations.show', $report->id),
                    icon: 'las la-check-circle text-emerald-500'
                ));
            }

            session()->flash('message', $submit ? 'Rapport soumis avec succès !' : 'Brouillon enregistré avec succès !');

            return redirect()->route('rapports.index');
        } catch (\Exception $e) {
            // Ajouter l'erreur au système de validation de Livewire
            $this->addError('permission', $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifie la contrainte d'unicité : un seul rapport par projet et par mois
     * @throws \Exception
     */
    private function checkUniqueReportConstraint()
    {
        // Déterminer la valeur à vérifier ('' pour 'all', sinon l'ID du projet)
        $projectValue = ($this->selected_project_id === 'all') ? '' : $this->selected_project_id;

        $exists = MonthlyReport::where('user_id', $this->user_id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->where('project_ids', $projectValue)
            ->exists();

        if ($exists) {
            $type = $this->selected_project_id === 'all' ? 'global (tous les projets)' : "pour le projet #{$this->selected_project_id}";
            // Lance une exception qui sera capturée par le système de validation
            throw new \Exception("Un rapport {$type} existe déjà pour ce mois-ci.");
        }
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
