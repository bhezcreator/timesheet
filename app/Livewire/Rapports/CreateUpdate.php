<?php

namespace App\Livewire\Rapports;

use App\Events\UniversalModelStatusChanged;
use App\Models\Activity;

use App\Models\MonthlyReport;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

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

    // protected function rules()
    // {
    //     // 1. Récupérer les identifiants des projets assignés à l'utilisateur
    //     $assignedProjectIds = User::find($this->user_id)         ?->projects()
    //         ->pluck('projects.id')
    //         ->toArray() ?? [];

    //     // 2. Autoriser la valeur 'all' en plus des IDs de projets assignés
    //     $allowedProjectValues = array_merge(['all'], $assignedProjectIds);

    //     return [
    //         'month' => 'required|integer|between:1,12',
    //         'year' => 'required|integer|min:2020',
    //         'report_date' => 'required|date',
    //         'objectives' => 'required|string|min:10',
    //         'achievements' => 'required|string|min:10',
    //         'next_actions' => 'required|string|min:10',
    //         'selected_project_id' => [
    //             'required',
    //             Rule::in($allowedProjectValues),
    //             'files.*' => [
    //                 'nullable',
    //                 'file',
    //                 'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp',
    //                 'max:5120' // 5 Mo maximum par fichier
    //             ],
    //         ],
    //     ];
    // }

    // 1. Ajouter des règles de validation renforcées
    protected function rules()
    {
        return [
            'files.*' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp',
                'max:5120', // 5 Mo
                function ($attribute, $value, $fail) {
                    // 2. Vérification MIME réelle
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $value->getRealPath());
                    finfo_close($finfo);

                    $allowedMimes = [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/csv',
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    if (!in_array($mimeType, $allowedMimes)) {
                        $fail("Le fichier {$value->getClientOriginalName()} n'est pas valide.");
                    }

                    // 3. Vérification de la signature du fichier
                    if (!$this->validateFileSignature($value)) {
                        $fail("Le fichier {$value->getClientOriginalName()} semble corrompu.");
                    }
                },
            ],
        ];
    }

    // 4. Validation de la signature du fichier
    protected function validateFileSignature($file): bool
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'rb');
        $bytes = fread($handle, 1024);
        fclose($handle);

        // Vérifier les signatures communes
        $signatures = [
            '%PDF' => 'application/pdf',
            'PK' => 'application/zip', // docx, xlsx sont des zip
            'ffd8ffe0' => 'image/jpeg',
            '89504e47' => 'image/png',
            // Ajouter d'autres signatures
        ];

        $hex = bin2hex(substr($bytes, 0, 4));
        foreach ($signatures as $sig => $mime) {
            if (strpos($hex, bin2hex($sig)) === 0) {
                return true;
            }
        }

        return false;
    }

    // 5. Sécurisation du stockage
    private function secureStoreFile(MonthlyReport $report, $file)
    {
        // Nettoyer le nom du fichier
        $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        $cleanName = substr($cleanName, 0, 255);

        // Ajouter un timestamp pour éviter les collisions
        $timestamp = now()->timestamp;
        $finalName = "{$timestamp}_{$cleanName}";

        return $report->addMedia($file->getRealPath())
            ->usingFileName($finalName)
            ->toMediaCollection('attachments');
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
    // public function deleteAttachment($mediaId)
    // {
    //    if ($this->ID_report) {
    //        $report = MonthlyReport::where('user_id', $this->user_id)->findOrFail($this->ID_report);
    //        $media = $report->media()->find($mediaId);
    //        if ($media) {
    //            $media->delete();
    //            $this->loadAttachments($report);
    //        }
    //    }
    // }

    public function deleteAttachment($mediaId)
    {
        // 1. Vérification de permission
        $this->checkPermissionOrFail("rapports.modifier");

        if (!$this->ID_report) {
            throw ValidationException::withMessages([
                'permission' => ["Aucun rapport associé."]
            ]);
        }

        // 2. Récupération sécurisée du rapport
        $report = MonthlyReport::where('user_id', $this->user_id)->findOrFail($this->ID_report);

        // 3. Vérification du statut (ne pas permettre sur rapport soumis)
        if ($report->status === 'soumis') {
            throw ValidationException::withMessages([
                'permission' => ["Impossible de modifier un rapport soumis."]
            ]);
        }

        // 4. Récupération et suppression sécurisée du média
        $media = $report->media()->find($mediaId);
        if (!$media) {
            throw ValidationException::withMessages([
                'permission' => ["Fichier non trouvé ou non autorisé."]
            ]);
        }

        // 5. Journalisation
        activity()
            ->performedOn($report)
            ->causedBy(Auth::user())
            ->withProperties([
                'file_name' => $media->file_name,
                'file_id' => $media->id
            ])
            ->log('Fichier supprimé');

        $media->delete();
        $this->loadAttachments($report);

        session()->flash('success', 'Fichier supprimé avec succès.');
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
    // public function getActivitiesProperty()
    // {
    //     // 1. Récupérer les identifiants des projets assignés à l'utilisateur
    //     $assignedProjectIds = User::find($this->user_id)
    //         ?->projects()
    //         ->pluck('projects.id') // Remplacez 'projects.id' par le nom correct si nécessaire
    //         ->toArray() ?? [];

    //     // 2. Construire la requête de base pour les activités de l'utilisateur
    //     $query = Activity::where('user_id', $this->user_id)
    //         ->whereYear('activity_date', $this->year)
    //         ->whereMonth('activity_date', $this->month);

    //     // 3. Appliquer le filtre de projet sélectionné tout en respectant ses affectations
    //     if ($this->selected_project_id !== 'all') {
    //         // Si un projet spécifique est choisi, on vérifie s'il fait partie de ses projets assignés
    //         if (in_array($this->selected_project_id, $assignedProjectIds)) {
    //             $query->where('project_id', $this->selected_project_id);
    //         } else {
    //             // Sécurité : Si le projet n'est pas assigné, on force une liste vide
    //             $query->whereRaw('1 = 0');
    //         }
    //     } else {
    //         // Si "Tous les projets" est sélectionné, on restreint uniquement à ses projets assignés
    //         $query->whereIn('project_id', $assignedProjectIds);
    //     }

    //     $activities = $query->with('project', 'activityType')->orderBy('activity_date', 'asc')->get();

    //     // Calcul des totaux d'heures
    //     $this->totalHours = $activities->sum('duration');
    //     if ($this->calculateOvertime && $this->totalHours > $this->standardHoursPerMonth) {
    //         $this->overtimeHours = $this->totalHours - $this->standardHoursPerMonth;
    //     } else {
    //         $this->overtimeHours = 0;
    //     }

    //     return $activities;
    // }
    public function getActivitiesProperty()
    {
        // 1. Validation stricte des paramètres
        $year = (int) $this->year;
        $month = (int) $this->month;

        // 2. Récupération sécurisée des projets assignés
        $assignedProjectIds = User::find($this->user_id)
            ?->projects()
            ->pluck('projects.id')
            ->toArray() ?? [];

        // 3. Nettoyage et validation de la sélection
        $selectedProject = $this->selected_project_id;
        if ($selectedProject !== 'all' && !in_array($selectedProject, $assignedProjectIds)) {
            // Sécurité : Projet non assigné -> retourner une collection vide
            return collect();
        }

        // 4. Construction sécurisée de la requête
        $query = Activity::where('user_id', $this->user_id)
            ->whereYear('activity_date', $year)
            ->whereMonth('activity_date', $month);

        // 5. Application du filtre de projet de manière sécurisée
        if ($selectedProject !== 'all') {
            $query->where('project_id', (int) $selectedProject);
        } elseif (!empty($assignedProjectIds)) {
            $query->whereIn('project_id', $assignedProjectIds);
        } else {
            // Aucun projet assigné -> retour vide
            return collect();
        }

        // 6. Exécution avec pagination pour limiter la charge
        $activities = $query->with(['project', 'activityType'])
            ->orderBy('activity_date', 'asc')
            ->limit(500) // Protection contre les requêtes massives
            ->get();

        // 7. Calcul sécurisé des totaux
        $this->totalHours = $activities->sum('duration');
        $this->calculateOvertimeHours();

        return $activities;
    }

    protected function calculateOvertimeHours(): void
    {
        if ($this->calculateOvertime && $this->totalHours > $this->standardHoursPerMonth) {
            $this->overtimeHours = $this->totalHours - $this->standardHoursPerMonth;
        } else {
            $this->overtimeHours = 0;
        }
    }

    // public function save($submit = false)
    // {
    //     try {
    //         if ($this->ID_report) {
    //             $this->checkPermissionOrFail("rapports.modifier");
    //         } else {
    //             $this->checkPermissionOrFail("rapports.creer");
    //         }

    //         $selected_project_id = ($this->selected_project_id === 'all') ? '' : $this->selected_project_id;
    //         $this->validate();

    //         // 🔍 VÉRIFICATION DE LA CONTRAINTE D'UNICITÉ
    //         if (!$this->ID_report) {
    //             $this->checkUniqueReportConstraint();
    //         }

    //         $data = [
    //             'user_id' => $this->user_id,
    //             'month' => $this->month,
    //             'year' => $this->year,
    //             'project_ids' => $selected_project_id,
    //             'report_date' => $this->report_date,
    //             'objectives' => $this->objectives,
    //             'achievements' => $this->achievements,
    //             'next_actions' => $this->next_actions,
    //             'status' => $submit ? 'soumis' : 'brouillon',
    //             'submitted_at' => $submit ? now() : null,
    //         ];

    //         // Sauvegarde ou Mise à jour du rapport
    //         $report = MonthlyReport::updateOrCreate(
    //             ['id' => $this->ID_report, 'user_id' => $this->user_id],
    //             $data
    //         );

    //         // Traitement des pièces jointes Spatie Media Library
    //         if (!empty($this->files)) {
    //             foreach ($this->files as $file) {
    //                 $report->addMedia($file->getRealPath())
    //                     ->usingFileName($file->getClientOriginalName())
    //                     ->toMediaCollection('attachments');
    //             }
    //             // Dans le contrôleur lors de l'ajout de fichiers
    //             activity()
    //                 ->performedOn($report)
    //                 ->causedBy(Auth::user())
    //                 ->withProperties([
    //                     'file_count' => $report->getMedia('attachments')->count(),
    //                     'file_names' => $report->getMedia('attachments')->pluck('file_name')->toArray()
    //                 ])
    //                 ->log('Fichiers joints ajoutés au rapport');
    //             $this->reset('files');
    //         }

    //         // Lier toutes les activités affichées à ce rapport
    //         Activity::whereIn('id', $this->activities->pluck('id'))->update([
    //             'monthly_report_id' => $report->id,
    //             'status' => $submit ? 'soumis' : 'brouillon',
    //         ]);

    //         if ($submit) {
    //             $supervise = $report->user->supervisor;

    //             event(new UniversalModelStatusChanged(
    //                 model: $report,
    //                 recipient: $supervise,
    //                 title: "Soumission du : " . $report->full_title,
    //                 messageContent: "Le colaborateur " . $report->user->name . ' ' . $report->user->first_name . " vient de soumetre son rapport.",
    //                 status: 'soumis',
    //                 comment: '',
    //                 routeUrl: route('validations.show', $report->id),
    //                 icon: 'las la-check-circle text-emerald-500'
    //             ));
    //         }

    //         session()->flash('message', $submit ? 'Rapport soumis avec succès !' : 'Brouillon enregistré avec succès !');

    //         return redirect()->route('rapports.index');
    //     } catch (\Exception $e) {
    //         // Ajouter l'erreur au système de validation de Livewire
    //         $this->addError('permission', $e->getMessage());
    //         return null;
    //     }
    // }
    public function save($submit = false)
    {
        try {
            // 1. Rate limiting
            $this->checkRateLimit();

            // 2. Validation des permissions
            $this->validatePermissions($submit);

            // 3. Validation des données
            $this->validate();

            // 4. Vérification des contraintes
            if (!$this->ID_report) {
                $this->checkUniqueReportConstraint();
            }

            // 5. Transaction DB
            DB::transaction(function () use ($submit) {
                // Préparation des données
                $data = $this->prepareData($submit);

                // Sauvegarde du rapport
                $report = MonthlyReport::updateOrCreate(
                    ['id' => $this->ID_report, 'user_id' => $this->user_id],
                    $data
                );

                // Traitement des fichiers
                $this->processAttachments($report);

                // Mise à jour des activités
                $this->updateActivities($report, $submit);

                // Envoi des notifications
                if ($submit) {
                    $this->sendSubmissionNotification($report);
                }
            });

            return $this->handleSuccess($submit);
        } catch (\Exception $e) {
            // 6. Gestion sécurisée des erreurs
            Log::error('Erreur sauvegarde rapport', [
                'user_id' => $this->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->addError('permission', $e->getMessage());
            return null;
        }
    }

    protected function checkRateLimit(): void
    {
        $key = 'report_save_' . $this->user_id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw new \Exception("Trop de tentatives. Veuillez attendre 5 minutes.");
        }
        RateLimiter::hit($key, 300);
    }

    protected function validatePermissions($submit): void
    {
        $permission = $this->ID_report ? "rapports.modifier" : "rapports.creer";
        $this->checkPermissionOrFail($permission);
    }

    protected function prepareData($submit): array
    {
        return [
            'user_id' => $this->user_id,
            'month' => (int) $this->month,
            'year' => (int) $this->year,
            'project_ids' => $this->selected_project_id === 'all' ? '' : (int) $this->selected_project_id,
            'report_date' => $this->report_date,
            'objectives' => strip_tags(trim($this->objectives)),
            'achievements' => strip_tags(trim($this->achievements)),
            'next_actions' => strip_tags(trim($this->next_actions)),
            'status' => $submit ? 'soumis' : 'brouillon',
            'submitted_at' => $submit ? now() : null,
        ];
    }

    protected function sendSubmissionNotification(MonthlyReport $report): void
    {
        // 1. Vérification des destinataires
        $supervisor = $report->user->supervisor;
        if (!$supervisor) {
            Log::warning('Aucun superviseur trouvé pour le rapport', [
                'report_id' => $report->id,
                'user_id' => $report->user_id
            ]);
            return;
        }

        // 2. Validation du statut
        if ($report->status !== 'soumis') {
            Log::warning('Tentative de notification pour rapport non soumis', [
                'report_id' => $report->id,
                'status' => $report->status
            ]);
            return;
        }

        // 3. Nettoyage des données pour l'événement
        $title = "Soumission du rapport : " . e($report->full_title);
        $message = "Le collaborateur " . e($report->user->name . ' ' . $report->user->first_name) .
            " vient de soumettre son rapport.";

        // 4. Envoi avec rate limiting
        try {
            event(new UniversalModelStatusChanged(
                model: $report,
                recipient: $supervisor,
                title: $title,
                messageContent: $message,
                status: 'soumis',
                comment: '',
                routeUrl: route('validations.show', $report->id),
                icon: 'las la-check-circle text-emerald-500'
            ));

            Log::info('Notification de soumission envoyée', [
                'report_id' => $report->id,
                'supervisor_id' => $supervisor->id
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification', [
                'report_id' => $report->id,
                'error' => $e->getMessage()
            ]);
            // Ne pas bloquer l'opération si la notification échoue
        }
    }

    /**
     * Vérifie la contrainte d'unicité : un seul rapport par projet et par mois
     * @throws \Exception
     */
    // private function checkUniqueReportConstraint()
    // {
    //     // Déterminer la valeur à vérifier ('' pour 'all', sinon l'ID du projet)
    //     $projectValue = ($this->selected_project_id === 'all') ? '' : $this->selected_project_id;

    //     $exists = MonthlyReport::where('user_id', $this->user_id)
    //         ->where('month', $this->month)
    //         ->where('year', $this->year)
    //         ->where('project_ids', $projectValue)
    //         ->exists();

    //     if ($exists) {
    //         $type = $this->selected_project_id === 'all' ? 'global (tous les projets)' : "pour le projet #{$this->selected_project_id}";
    //         // Lance une exception qui sera capturée par le système de validation
    //         throw new \Exception("Un rapport {$type} existe déjà pour ce mois-ci.");
    //     }
    // }

    private function checkUniqueReportConstraint()
    {
        // 1. Validation des données d'entrée
        $month = (int) $this->month;
        $year = (int) $this->year;

        if ($month < 1 || $month > 12 || $year < 2020) {
            throw new \Exception("Période invalide.");
        }

        // 2. Détermination sécurisée de la valeur projet
        $projectValue = $this->selected_project_id === 'all' ? '' : (int) $this->selected_project_id;

        // 3. Vérification avec verrouillage pour éviter les conditions de concurrence
        $exists = MonthlyReport::where('user_id', $this->user_id)
            ->where('month', $month)
            ->where('year', $year)
            ->where('project_ids', $projectValue)
            ->lockForUpdate() // Verrouillage pessimiste
            ->exists();

        if ($exists) {
            $type = $this->selected_project_id === 'all'
                ? 'global (tous les projets)'
                : "pour le projet #{$this->selected_project_id}";
            throw new \Exception("Un rapport {$type} existe déjà pour ce mois-ci.");
        }
    }

    // public function render()
    // {
    //     $user = User::find($this->user_id);
    //     return view('livewire.rapports.create-update', [
    //         'projects' => $user->projects()->get(),
    //         'activitiesList' => $this->activities,
    //     ]);
    // }
    public function render()
    {
        // 1. Récupération sécurisée des projets
        $user = User::with('projects')->find($this->user_id);
        $projects = $user?->projects()->where('status', 'active')->get() ?? collect();

        // 2. Filtrage supplémentaire
        $filteredProjects = $projects->filter(function ($project) {
            // Vérification supplémentaire de l'affectation
            return $project->users()->where('user_id', $this->user_id)->exists();
        });

        // 3. Récupération sécurisée des activités
        $activitiesList = $this->getFilteredActivities();

        return view('livewire.rapports.create-update', [
            'projects' => $filteredProjects,
            'activitiesList' => $activitiesList,
        ]);
    }

    protected function getFilteredActivities()
    {
        // Implémentation sécurisée
        $query = Activity::where('user_id', $this->user_id);

        if ($this->selected_project_id !== 'all') {
            $query->where('project_id', (int) $this->selected_project_id);
        }

        return $query->whereYear('activity_date', $this->year)
            ->whereMonth('activity_date', $this->month)
            ->with(['project', 'activityType'])
            ->orderBy('activity_date', 'asc')
            ->limit(500)
            ->get();
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
