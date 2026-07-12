<?php

namespace App\Livewire\Projects;

use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AttributesProject extends Component
{
    use WithPagination;

    // Utilisateur cible
    public int $userId;
    public User $user;

    // État du bouton de sauvegarde (True = Masqué/Désactivé, False = Visible/Actif)
    public bool $hideBtn = true;

    // Tableaux de sélection réactifs [ID => true/false]
    public array $selectedProjects = [];
    public array $selectedSubProjects = [];

    // Rôles spécifiques par projet [project_id => 'responsable'|'superviseur'|'personnel']
    public array $projectRoles = [];

    // Recherche réactive
    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(int $userId)
    {
        $this->userId = $userId;
        $this->user = User::findOrFail($userId);

        // Pré-charger les affectations existantes de la base de données
        $this->loadExistingAssignments();
        $this->checkButtonStatus();
    }

    /**
     * Charge l'état actuel des affectations de l'utilisateur pour initialiser l'interface.
     */
    private function loadExistingAssignments()
    {
        // Projets existants
        $existingProjects = DB::table('project_user')
            ->where('user_id', $this->userId)
            ->get();

        foreach ($existingProjects as $pUser) {
            $this->selectedProjects[$pUser->project_id] = true;
            $this->projectRoles[$pUser->project_id] = $pUser->role;
        }

        // Sous-projets existants
        $existingSubs = DB::table('sub_project_user')
            ->where('user_id', $this->userId)
            ->pluck('sub_project_id')
            ->toArray();

        foreach ($existingSubs as $subId) {
            $this->selectedSubProjects[$subId] = true;
        }
    }

    /**
     * CENTRALISATION : Calcule l'état de visibilité du bouton en fonction des projets cochés.
     * Si aucun projet n'est coché (ou s'ils sont décochés), hideBtn passe à true.
     */
    public function checkButtonStatus()
    {
        // array_filter nettoie le tableau en supprimant les valeurs équivalentes à false
        $activeProjects = array_filter($this->selectedProjects);

        // hideBtn est VRAI (bouton caché) si la liste des projets actifs est VIDE
        $this->hideBtn = empty($activeProjects);
    }

    /**
     * Hook magique Livewire : S'exécute automatiquement après TOUTE modification
     * d'une propriété publique du composant (pratique pour le biding direct par case à cocher).
     */
    public function updated()
    {
        $this->checkButtonStatus();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Logique de bascule pour un projet complet
     */
    public function toggleProject(int $projectId)
    {
        $isActive = $this->selectedProjects[$projectId] ?? false;

        if (!$isActive) {
            // Si on décoche le projet, on décoche obligatoirement tous ses sous-projets
            $subProjectIds = SubProject::where('project_id', $projectId)->pluck('id')->toArray();
            foreach ($subProjectIds as $subId) {
                $this->selectedSubProjects[$subId] = false;
            }
            unset($this->projectRoles[$projectId]);
        } else {
            // Rôle par défaut à l'activation
            if (!isset($this->projectRoles[$projectId])) {
                $this->projectRoles[$projectId] = 'personnel';
            }
        }

        // Recalculer immédiatement l'état du bouton après l'action
        $this->checkButtonStatus();
    }

    /**
     * Logique automatique : L'attribution d'un sous-projet coche automatiquement son parent.
     */
    public function toggleSubProject(int $subProjectId, int $projectId)
    {
        $isSubActive = $this->selectedSubProjects[$subProjectId] ?? false;

        if ($isSubActive) {
            // Règle métier : Forcer l'activation du parent si un sous-projet est choisi
            $this->selectedProjects[$projectId] = true;
            if (!isset($this->projectRoles[$projectId])) {
                $this->projectRoles[$projectId] = 'personnel';
            }
        }

        // Recalculer immédiatement l'état du bouton après l'action
        $this->checkButtonStatus();
    }

    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ["Action non autorisée : Privilèges insuffisants pour gérer les affectations."]
        ]);
    }

    public function save()
    {
        $this->checkPermissionOrFail("projets.attribuer");

        // 1. Filtrer pour ne garder que les projets réellement cochés à True
        $activeProjects = array_filter($this->selectedProjects);

        // 2. Préparer et valider les rôles uniquement pour les projets restants cochés
        $projectSyncData = [];
        foreach ($activeProjects as $projectId => $checked) {
            $role = $this->projectRoles[$projectId] ?? 'personnel';

            // Sécurité : Validation de l'énumération par rapport à votre schéma SQL
            if (!in_array($role, ['responsable', 'superviseur', 'personnel'])) {
                throw ValidationException::withMessages([
                    "projectRoles.{$projectId}" => ["Le rôle sélectionné pour ce projet est invalide."]
                ]);
            }

            $projectSyncData[$projectId] = [
                'role'        => $role,
                'assigned_at' => now()->format('Y-m-d'),
                'updated_at'  => now(),
            ];
        }

        // 3. Préparer la liste des sous-projets restants cochés
        $subProjectSyncData = [];
        foreach (array_filter($this->selectedSubProjects) as $subId => $checked) {
            $subProjectSyncData[] = $subId;
        }

        // 4. Exécution de la transaction BDD
        DB::transaction(function () use ($projectSyncData, $subProjectSyncData) {
            $this->user->projects()->sync($projectSyncData);
            $this->user->subProjects()->sync($subProjectSyncData);
        });

        session()->flash('success', "Les affectations de projets pour {$this->user->first_name} {$this->user->name} ont été mises à jour.");

        // Rafraîchir l'état après la sauvegarde
        $this->checkButtonStatus();
    }

    public function render()
    {
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $this->search) . '%';

        // Récupérer les projets filtrés avec leurs sous-projets (Eager Loading)
        $projects = Project::query()
            ->with(['subProjects'])
            ->where('name', 'like', $searchTerm)
            ->orWhere('code', 'like', $searchTerm)
            ->latest()
            ->paginate(4);

        return view('livewire.projects.attributes-project', [
            'projects' => $projects,
        ]);
    }
}
