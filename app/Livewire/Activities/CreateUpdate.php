<?php

namespace App\Livewire\Activities;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use App\Services\AppSettingsService;
use App\Services\TimesheetLockService;
use App\Services\CalendarBusinessService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateUpdate extends Component
{
    // Variables d'état
    public ?int $activityId = null;
    public bool $isEditMode = false;

    // Champs du formulaire liés au modèle
    public string $titre = '';
    public ?int $project_id = null;
    public ?int $sub_project_id = null;
    public ?int $activity_type_id = null;
    public string $activity_date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $description = '';

    // Collections pour l'affichage dynamique
    public $projects = [];
    public $subProjects = [];
    public $activityTypes = [];

    // User
    public User $user;

    /**
     * Initialisation du composant.
     */
    public function mount(TimesheetLockService $lockService, ?int $activityId = null)
    {
        $this->user = User::find(Auth::id());

        $this->activityId = $activityId;
        $this->isEditMode = !is_null($activityId);

        // 1. Contrôle de sécurité et de droits d'accès
        if ($this->isEditMode) {
            $this->checkPermissionOrFail('activites.modifier');
            $activity = Activity::where('user_id', $this->user->id)->findOrFail($this->activityId);

            // Sécurité : Impossible de modifier une activité soumise pour approbation ou déjà verrouillée
            if ($activity->status !== 'brouillon' && $activity->status !== 'rejeté') {
                throw ValidationException::withMessages([
                    'activity' => ["Modification interdite : Cette activité a déjà été transmise pour validation."]
                ]);
            }

            if ($lockService->isDateLocked($activity->activity_date)) {
                throw ValidationException::withMessages([
                    'activity' => ["Action impossible : La période contenant cette activité est clôturée."]
                ]);
            }

            // Hydratation du formulaire
            $this->titre = $activity->titre;
            $this->project_id = $activity->project_id;
            $this->sub_project_id = $activity->sub_project_id;
            $this->activity_type_id = $activity->activity_type_id;
            $this->activity_date = $activity->activity_date->format('Y-m-d');
            $this->start_time = Carbon::parse($activity->start_time)->format('H:i');
            $this->end_time = Carbon::parse($activity->end_time)->format('H:i');
            $this->description = $activity->description;

            // Charger les sous-projets dépendants du projet sélectionné
            $this->subProjects = SubProject::query()
                ->where('project_id', $this->project_id)
                ->whereHas('users', function ($query) {
                    $query->where('user_id', $this->user->id);
                })
                ->orderBy('name')
                ->get();
        } else {
            $this->checkPermissionOrFail('activites.creer');
            $this->activity_date = Carbon::today()->format('Y-m-d');
        }

        // Chargement des données d'alimentation pour les listes déroulantes
        // Récupère les projets actifs liés à l'utilisateur connecté
        $this->projects = Project::query()
            ->where('status', 'active') // Ajusté selon votre énumération précédente ('Actif' avec majuscule)
            ->whereHas('users', function ($query) {
                $query->where('user_id', $this->user->id); // Sécurité : exclut les anciens projets terminés pour cet utilisateur
            })
            ->orderBy('name')
            ->get();
        $this->activityTypes = ActivityType::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Écouteur réactif sur le changement de projet pour mettre à jour les sous-projets liés.
     */
    public function updatedProjectId($value)
    {
        // 1. On réinitialise le sous-projet sélectionné pour éviter les conflits
        $this->sub_project_id = null;

        // 2. Si une valeur est présente, on charge les sous-projets liés à l'utilisateur connecté
        $this->subProjects = $value
            ? SubProject::query()
            ->where('project_id', $value)
            ->whereHas('users', function ($query) {
                $query->where('user_id', $this->user->id);
            })
            ->orderBy('name')
            ->get()
            : [];
    }


    /**
     * Règles de validation standardisées.
     */
    protected function rules()
    {
        return [
            'titre' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9\-\._ a-z0-9àâäéèêëîïôöùûüçÂÆÇÈÉÊËÎÏÔŒÙÛÜ]+$/i'],
            'project_id' => ['required', 'exists:projects,id'],
            'sub_project_id' => ['nullable', 'exists:sub_projects,id'],
            'activity_type_id' => ['required', 'exists:activity_types,id'],
            'activity_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'description' => ['nullable', 'string', 'max:1000', 'regex:/^[a-z0-9\-\._ a-z0-9àâäéèêëîïôöùûüçÂÆÇÈÉÊËÎÏÔŒÙÛÜ]+$/i'],
        ];
    }

    /**
     * Enregistre ou modifie l'activité en appliquant le service AppSettingsService.
     */
    public function save(AppSettingsService $settingsService, TimesheetLockService $lockService, CalendarBusinessService $calendarService)
    {
        $this->validate();
        $carbonDate = Carbon::parse($this->activity_date);

        // --- SECTION VALIDATIONS VIA LE SERVICE DE PARAMÈTRES (AppSettingsService) ---
        // 1. Description obligatoire
        if ($settingsService->get('timesheet_require_description') && empty(trim($this->description))) {
            throw ValidationException::withMessages(['description' => ["Une description détaillée est requise par la configuration système."]]);
        }

        // 2. Calcul et validation de la durée
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $calculatedDuration = round($start->diffInMinutes($end) / 60, 2);

        $maxHoursAllowed = $settingsService->get('timesheet_max_hours_per_day', 8);
        if ($calculatedDuration > $maxHoursAllowed) {
            throw ValidationException::withMessages(['end_time' => ["La durée calculée ({$calculatedDuration}h) dépasse la limite maximale quotidienne autorisée ({$maxHoursAllowed}h)."]]);
        }

        // 3. Saisie le week-end
        if ($carbonDate->isWeekend() && !$settingsService->get('time_allow_weekend_logging')) {
            throw ValidationException::withMessages(['activity_date' => ["La saisie d'activités durant le week-end est désactivée."]]);
        }

        // 4. Saisie sur date future
        if ($carbonDate->isFuture() && !$settingsService->get('timesheet_allow_future_logging')) {
            throw ValidationException::withMessages(['activity_date' => ["Le système interdit la planification anticipée sur des dates futures."]]);
        }

        // 5. Restriction sur Jour verrouillé / Clôture mensuelle
        if ($lockService->isDateLocked($carbonDate)) {
            throw ValidationException::withMessages(['activity_date' => ["Cette journée correspond à une période verrouillée ou close."]]);
        }

        // 6. Utilisation du service pour rejeter la saisie si la date n'est pas un jour ouvré théorique
        $validWorkingDates = $calendarService->getWorkingDatesArray($carbonDate->month, $carbonDate->year);

        if (!in_array($this->activity_date, $validWorkingDates)) {
            throw ValidationException::withMessages([
                'activity_date' => ["Erreur de calendrier : La date sélectionnée correspond à un jour non ouvré (Week-end)."]
            ]);
        }

        // EXEMPLE 2 : Récupérer le nom du jour en français si vous voulez l'historiser ou l'afficher
        // $nomDuJour = $calendarService->getDayNameInFrench($this->activity_date);

        // --- ENREGISTREMENT ---
        $data = [
            'titre' => trim($this->titre),
            'user_id' => Auth::id(),
            'project_id' => $this->project_id,
            'sub_project_id' => $this->sub_project_id,
            'activity_type_id' => $this->activity_type_id,
            'activity_date' => $this->activity_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration' => $calculatedDuration,
            'description' => trim($this->description),
            'status' => 'brouillon', // Reste en brouillon jusqu'à soumission globale
        ];

        if ($this->isEditMode) {
            $activity = Activity::where('user_id', Auth::id())->findOrFail($this->activityId);
            $activity->update($data);
            session()->flash('success', 'Activité mise à jour avec succès.');
        } else {
            Activity::create($data);
            session()->flash('success', 'Activité enregistrée avec succès.');
        }

        // Redirection instantanée vers le tableau de bord
        return $this->redirectRoute('dashboard', navigate: true);
    }

    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ["Action non autorisée : Droits d'accès insuffisants."]
        ]);
    }

    public function render(CalendarBusinessService $calendarService)
    {
        $currentDate = Carbon::parse($this->activity_date);

        return view('livewire.activities.create-update', [
            'workingDaysCount' => $calendarService->getWorkingDaysCount($currentDate->month, $currentDate->year),
            'monthLabel' => $calendarService->getMonthAndYearInFrench($currentDate)['mois']
        ]);
    }
}
