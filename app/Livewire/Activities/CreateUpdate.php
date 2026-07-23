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

    // ID User
    public int $user_id;

    // Calcul de pourcentage d'avancement du projet par rapport à user actuel
    public string $currentProjectName = '';
    public float $projectProgressPercentage = 0;

    // Calcul les jours de travail
    public string $monthLabel = '';
    public int $workingDaysCount = 0;
    public int $userLoggedDaysCount = 0; // Contient le nombre de jours déjà saisis par l'agent

    /**
     * Initialisation du composant.
     */
    public function mount(TimesheetLockService $lockService, ?int $activityId = null)
    {
        $this->user_id = Auth::id();

        $this->activityId = $activityId;
        $this->isEditMode = !is_null($activityId);

        // 1. Contrôle de sécurité et de droits d'accès
        if ($this->isEditMode) {
            $this->checkPermissionOrFail('activites.modifier');
            $activity = Activity::where('user_id', $this->user_id)->findOrFail($this->activityId);

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
                    $query->where('user_id', $this->user_id);
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
                $query->where('user_id', $this->user_id); // Sécurité : exclut les anciens projets terminés pour cet utilisateur
            })
            ->orderBy('name')
            ->get();
        $this->activityTypes = ActivityType::where('is_active', true)->orderBy('name')->get();

        // RCamcum les jours de travail
        $this->calculateMonthlyWorkingDays(app(\App\Services\CalendarBusinessService::class));
    }

    /**
     * Écouteur réactif sur le changement de projet pour mettre à jour les sous-projets liés.
     * et calcul d'avancement du projet rar rapport à user en ligne
     */
    public function updatedProjectId($value)
    {
        // 1. Réinitialisation du sous-projet sélectionné
        $this->sub_project_id = null;
        $this->projectProgressPercentage = 0;
        $this->currentProjectName = '';

        if ($value) {
            $project = Project::find($value);
            $this->currentProjectName = $project ? $project->name : '';

            // 2. Chargement des sous-projets filtrés pour l'utilisateur en ligne
            $this->subProjects = SubProject::query()
                ->where('project_id', $value)
                ->whereHas('users', function ($query) {
                    $query->where('user_id', $this->user_id);
                })
                ->orderBy('name')
                ->get();

            // 3. --- CALCUL DU POURCENTAGE DE PROGRESSION DE L'AGENT ---
            // Récupération des services nécessaires à la volée
            $calendarService = app(\App\Services\CalendarBusinessService::class);
            $settingsService = app(\App\Services\AppSettingsService::class);

            // Récupération des heures cibles quotidiennes (ex: 8h par jour)
            $workdayHours = (float) $settingsService->get('time_workday_hours', 8.0);

            // Nombre de jours ouvrés pour le mois actuel
            $totalWorkingDays = $calendarService->getWorkingDaysCount(now()->month, now()->year);

            // Volume d'heures total que l'agent doit effectuer dans le mois (ex: 22 jours * 8h = 176h)
            $totalMonthlyRequiredHours = $totalWorkingDays * $workdayHours;

            if ($totalMonthlyRequiredHours > 0) {
                // Somme des heures déjà déclarées par cet agent sur ce projet précis pour le mois actuel
                $hoursLoggedOnProject = Activity::query()
                    ->where('user_id', $this->user_id)
                    ->where('project_id', $value)
                    ->whereMonth('activity_date', now()->month)
                    ->whereYear('activity_date', now()->year)
                    ->sum('duration');

                // Calcul du pourcentage d'imputation de l'agent sur ce projet
                $this->projectProgressPercentage = round((100 * $hoursLoggedOnProject) / $totalMonthlyRequiredHours, 1);
            }
        } else {
            $this->subProjects = [];
        }
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
            'description' => ['nullable', 'string', 'max:1000', 'regex:/^[a-z0-9\-\._ a-z0-9àâäéèêëîïôöùûü;:!,?çÂÆÇÈÉÊËÎÏÔŒÙÛÜ]+$/i'],
        ];
    }

    /**
     * Enregistre ou modifie l'activité en appliquant le service AppSettingsService.
     */
    public function save(AppSettingsService $settingsService, TimesheetLockService $lockService, CalendarBusinessService $calendarService)
    {
        $carbonDate = Carbon::parse($this->activity_date);

        // 1. Calcul immédiat de la durée pour les validations suivantes
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $calculatedDuration = round($start->diffInMinutes($end) / 60, 2);

        // Remplacez l'ancien appel par celui-ci :
        $maxWeeklyHours = (float) $settingsService->get('time_workweek_hours', 40.0);

        if ($this->exceedsWeeklyHoursCeiling($this->activity_date, $calculatedDuration, $maxWeeklyHours, $settingsService)) {
            throw ValidationException::withMessages([
                'end_time' => ["Limite hebdomadaire atteinte : L'ajout de cette activité ferait dépasser le plafond global autorisé de {$maxWeeklyHours}h pour cette semaine."]
            ]);
        }

        // AJOUT 1 : Gestion dynamique de la validation de la description avant le validate()
        if (!$settingsService->get('timesheet_require_description')) {
            // Si la description n'est pas requise, on retire sa règle regex pour qu'elle puisse être vide sans planter
            $currentRules = $this->rules();
            $currentRules['description'] = ['nullable', 'string', 'max:1000'];
            $this->validate($currentRules);
        } else {
            $this->validate();
            // Validation manuelle de sécurité si le champ ne contient que des espaces
            if (empty(trim($this->description))) {
                throw ValidationException::withMessages(['description' => ["Une description détaillée est requise par la configuration système."]]);
            }
        }

        // --- 1.2. BLOCAGE DE LA SAISIE DU MOIS EN COURS À PARTIR DU 25 ---
        $lockDay = (int) $settingsService->get('timesheet_lock_day_of_month', 25);
        $today = Carbon::today();

        // Si l'activité appartient au mois civil actuel
        if ($carbonDate->isCurrentMonth()) {
            // Si nous avons atteint ou dépassé le jour limite (ex: le 25), on bloque tout
            if ($today->day >= $lockDay) {
                throw ValidationException::withMessages([
                    'activity_date' => ["Période clôturée : La saisie pour le mois en cours est verrouillée du {$lockDay} jusqu'à la fin du mois."]
                ]);
            }
        }

        // --- 1.3. VÉRIFICATION DU VERROUILLAGE DES MOIS PASSÉS ---
        // Si l'activité appartient à un mois antérieur au mois en cours
        if ($carbonDate->format('Y-m') < $today->format('Y-m')) {

            // Cas A : L'activité appartient exactement au mois précédent (M-1) et on a dépassé le jour limite
            if ($carbonDate->format('Y-m') === $today->copy()->subMonth()->format('Y-m') && $today->day > $lockDay) {
                throw ValidationException::withMessages([
                    'activity_date' => ["Action impossible : La période du mois précédent est définitivement verrouillée depuis le {$lockDay} de ce mois."]
                ]);
            }

            // Cas B : L'activité appartient à un mois encore plus ancien (M-2, M-3...) -> Toujours bloqué
            if ($carbonDate->format('Y-m') < $today->copy()->subMonth()->format('Y-m')) {
                throw ValidationException::withMessages([
                    'activity_date' => ["Opération refusée : Impossible de modifier des données d'un mois clos historiquement."]
                ]);
            }
        }


        // 2. Restriction sur Jour verrouillé / Clôture mensuelle (Prioritaire pour économiser le serveur)
        if ($lockService->isDateLocked($carbonDate)) {
            throw ValidationException::withMessages(['activity_date' => ["Cette journée correspond à une période verrouillée ou close."]]);
        }

        // 3. Saisie sur date future
        if ($carbonDate->isFuture() && !$settingsService->get('timesheet_allow_future_logging')) {
            throw ValidationException::withMessages(['activity_date' => ["Le système interdit la planification anticipée sur des dates futures."]]);
        }

        // 4. Saisie le week-end
        $allowWeekend = $settingsService->get('time_allow_weekend_logging');
        if ($carbonDate->isWeekend() && !$allowWeekend) {
            throw ValidationException::withMessages(['activity_date' => ["La saisie d'activités durant le week-end est désactivée."]]);
        }

        // AJOUT 2 : Exécuter la validation des jours ouvrés UNIQUEMENT si ce n'est pas un week-end autorisé
        if (!$carbonDate->isWeekend()) {
            $validWorkingDates = $calendarService->getWorkingDatesArray($carbonDate->month, $carbonDate->year);
            if (!in_array($this->activity_date, $validWorkingDates)) {
                throw ValidationException::withMessages([
                    'activity_date' => ["Erreur de calendrier : La date sélectionnée est invalide pour cette période."]
                ]);
            }
        }

        // 5. Validation de la durée maximale autorisée pour UNE SEULE activité
        $maxHoursAllowed = $settingsService->get('timesheet_max_hours_per_day', 8);
        if ($calculatedDuration > $maxHoursAllowed) {
            throw ValidationException::withMessages(['end_time' => ["La durée calculée ({$calculatedDuration}h) dépasse la limite maximale quotidienne autorisée ({$maxHoursAllowed}h)."]]);
        }

        // 6. VÉRIFICATION DU PLAFOND QUOTIDIEN GLOBAL (Cumul de toutes les activités de la journée)
        if ($this->exceedsDailyHoursCeiling($this->activity_date, $calculatedDuration, $maxHoursAllowed)) {
            throw ValidationException::withMessages([
                'end_time' => ["Limite atteinte : Le cumul des heures pour cette journée dépasse le plafond maximal global autorisé de {$maxHoursAllowed}h par jour."]
            ]);
        }

        // 7. VÉRIFICATION DU CHEVAUCHEMENT HORAIRE (Requête SQL en dernier pour optimiser les performances)
        if ($this->hasTimeOverlap($this->activity_date, $this->start_time, $this->end_time)) {
            throw ValidationException::withMessages([
                'start_time' => ["Conflit d'horaire : Vous avez déjà une activité enregistrée qui chevauche la tranche " . $this->start_time . " - " . $this->end_time . " pour cette journée."]
            ]);
        }

        // --- ENREGISTREMENT ---
        $data = [
            'titre' => trim($this->titre),
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'sub_project_id' => $this->sub_project_id,
            'activity_type_id' => $this->activity_type_id,
            'activity_date' => $this->activity_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration' => $calculatedDuration,
            'description' => $this->description ? trim($this->description) : null,
            'status' => 'brouillon',
        ];

        if ($this->isEditMode) {
            $activity = Activity::where('user_id', $this->user_id)->findOrFail($this->activityId);
            $activity->update($data);
            session()->flash('success', 'Activité mise à jour avec succès.');
        } else {
            Activity::create($data);
            session()->flash('success', 'Activité enregistrée avec succès.');
        }

        return $this->redirectRoute('activities.index', navigate: true);
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

    public function render()
    {
        return view('livewire.activities.create-update');
    }

    /**
     * RÈGLE A : Vérifie si la plage horaire soumise chevauche une activité existante.
     * Formule mathématique : (DébutA < FinB) ET (FinA > DébutB)
     */
    protected function hasTimeOverlap(string $date, string $startTime, string $endTime): bool
    {
        return Activity::query()
            ->where('user_id', $this->user_id)
            ->whereDate('activity_date', $date)
            // On ignore la ligne en cours si on modifie
            ->when($this->isEditMode, function ($query) {
                $query->where('id', '!=', $this->activityId);
            })
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();
    }

    /**
     * RÈGLE B : Vérifie si le cumul d'heures de la journée dépasse le plafond autorisé.
     */
    protected function exceedsDailyHoursCeiling(string $date, float $newDuration, float $maxHoursAllowed): bool
    {
        // Calcul de la somme des heures déjà enregistrées pour ce jour-là
        $alreadyLoggedHours = Activity::query()
            ->where('user_id', $this->user_id)
            ->whereDate('activity_date', $date)
            ->when($this->isEditMode, function ($query) {
                $query->where('id', '!=', $this->activityId);
            })
            ->sum('duration');

        return ($alreadyLoggedHours + $newDuration) > $maxHoursAllowed;
    }

    /**
     * RÈGLE C : Vérifie si le cumul d'heures de la semaine dépasse le plafond autorisé.
     */
    protected function exceedsWeeklyHoursCeiling(string $date, float $newDuration, float $maxWeeklyHours, AppSettingsService $settingsService): bool
    {
        $carbonDate = \Carbon\Carbon::parse($date)->startOfDay();

        // Récupération du réglage : 0 = Dimanche, 1 = Lundi
        $firstDaySetting = (int) $settingsService->get('time_first_day_of_week', 1);

        if ($firstDaySetting === 0) {
            // Si la semaine commence le DIMANCHE :
            // Si aujourd'hui est un dimanche, le début est aujourd'hui, sinon on recule au dimanche précédent
            $startOfWeek = $carbonDate->dayOfWeek === \Carbon\Carbon::SUNDAY
                ? $carbonDate->copy()
                : $carbonDate->copy()->modify('last sunday');

            $endOfWeek = $startOfWeek->copy()->modify('next saturday')->endOfDay();
        } else {
            // Si la semaine commence le LUNDI (Comportement natif standard)
            $startOfWeek = $carbonDate->copy()->startOfWeek();
            $endOfWeek = $carbonDate->copy()->endOfWeek();
        }

        // Calcul de la somme des heures sur cette plage de dates stricte
        $weeklyLoggedHours = Activity::query()
            ->where('user_id', $this->user_id)
            ->whereBetween('activity_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->when($this->isEditMode, function ($query) {
                $query->where('id', '!=', $this->activityId);
            })
            ->sum('duration');

        return ($weeklyLoggedHours + $newDuration) > $maxWeeklyHours;
    }

    /**
     * Calcule les indicateurs de jours ouvrés pour le mois de l'activité en cours.
     */
    public function calculateMonthlyWorkingDays(CalendarBusinessService $calendarService)
    {
        // 1. Récupération de la date cible (date de l'activité ou date du jour par défaut)
        $targetDate = \Carbon\Carbon::parse($this->activity_date ?: now());

        // 2. Récupération du libellé du mois en français (ex: "Juillet") et de l'année
        $dateDetails = $calendarService->getMonthAndYearInFrench($targetDate);
        $this->monthLabel = $dateDetails['mois'] . ' ' . $dateDetails['annee'];

        // 3. Calcul du nombre de jours ouvrés théoriques du mois (hors week-ends)
        $this->workingDaysCount = $calendarService->getWorkingDaysCount($targetDate->month, $targetDate->year);

        // 4. Calcul du nombre de jours uniques où l'utilisateur connecté a déjà enregistré des activités ce mois-ci
        $this->userLoggedDaysCount = \App\Models\Activity::query()
            ->where('user_id', $this->user_id)
            ->whereMonth('activity_date', $targetDate->month)
            ->whereYear('activity_date', $targetDate->year)
            ->distinct()
            ->count('activity_date');
    }
}
