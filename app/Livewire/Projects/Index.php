<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Champs de formulaire indexés sur le modèle Project
    public string $code = '';

    public string $name = '';

    public string $description = '';

    public ?int $manager_id = null;

    public string $start_date = '';

    public string $end_date = '';

    public string $status = 'brouillon';

    public ?int $projectId = null;

    // Gestion Modal
    public bool $showModal = false;

    public bool $showDeleteModal = false;

    // Variables de suppression
    public ?int $deleteId = null;

    public ?string $deleteName = null;

    // Recherche réactive
    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                // 1. Validation d'unicité insensible à la casse
                function ($attribute, $value, $fail) {
                    $exists = Project::whereRaw('LOWER(code) = ?', [strtolower($value)])
                        ->when($this->projectId, function ($query) {
                            $query->where('id', '!=', $this->projectId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('Le code doit être unique (insensible à la casse).');
                    }
                },
                'regex:/^[a-z0-9\-\._]+$/i',
                'not_in:'.implode(',', ['admin', 'test', 'demo']), // Mots interdits
            ],
            // ... autres règles
        ];
    }

    // 2. Normalisation du code avant sauvegarde
    protected function normalizeCode(string $code): string
    {
        // Nettoyer et normaliser
        $code = trim($code);
        $code = strtoupper($code); // Forcer en majuscules
        $code = preg_replace('/[^A-Z0-9\-\._]/', '', $code);

        return $code;
    }

    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ['Action non autorisée : Privilèges insuffisants pour exécuter cette opération.'],
        ]);
    }

    // public function render()
    // {
    //     $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $this->search) . '%';

    //     $projects = Project::query()
    //         ->with(['manager', 'subProjects', 'users'])
    //         ->where(function ($query) use ($searchTerm) {
    //             $query->where('name', 'like', $searchTerm)
    //                 ->orWhere('code', 'like', $searchTerm)
    //                 ->orWhere('status', 'like', $searchTerm);
    //         })
    //         ->latest()
    //         ->paginate(2);

    //     $managers = User::query()
    //         ->where('is_active', true)
    //         ->orderBy('name')
    //         ->get();

    //     return view('livewire.projects.index', [
    //         'projects' => $projects,
    //         'managers' => $managers,
    //     ]);
    // }
    public function render()
    {
        // 1. Validation des paramètres de pagination
        $page = request()->get('page', 1);
        if (! is_numeric($page) || $page < 1) {
            $page = 1;
        }

        // 2. Limitation du nombre de résultats
        $perPage = min(2, 50); // Maximum 50 par page

        $projects = Project::query()
            ->with(['manager', 'subProjects', 'users'])
            ->when(! empty($this->search), function ($query) {
                $this->applySearchFilter($query);
            })
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        // 3. Sécurisation des managers
        $managers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(100) // Limitation
            ->get();

        return view('livewire.projects.index', [
            'projects' => $projects,
            'managers' => $managers,
        ]);
    }

    protected function applySearchFilter($query)
    {
        $search = trim($this->search);
        $search = preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search);

        $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', '%'.$search.'%')
                ->orWhere('code', 'LIKE', '%'.$search.'%')
                ->orWhere('status', 'LIKE', '%'.$search.'%');
        });
    }

    // 5. Filtrage par accès utilisateur
    protected function applyUserAccessFilter($projects)
    {
        if (Gate::allows('admin')) {
            return $projects;
        }

        $userId = Auth::id();

        // Filtrer uniquement les projets où l'utilisateur est membre ou manager
        return $projects->filter(function ($project) use ($userId) {
            return $project->manager_id === $userId ||
                $project->users()->where('user_id', $userId)->exists();
        });
    }

    public function openModal()
    {
        $this->checkPermissionOrFail('projets.creer');
        $this->resetForm();
        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    public function edit($id)
    {
        $this->checkPermissionOrFail('projets.modifier');

        // 1. Récupération avec vérification d'accès
        $project = Project::findOrFail($id);
        $this->validateAccess($project);

        $this->projectId = $project->id;
        $this->code = $project->code;
        $this->name = $project->name;
        $this->description = $project->description ?? '';
        $this->manager_id = $project->manager_id;
        $this->start_date = $project->start_date ? Carbon::parse($project->start_date)->format('Y-m-d') : '';
        $this->end_date = $project->end_date ? Carbon::parse($project->end_date)->format('Y-m-d') : '';
        $this->status = $project->status;

        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    public function save()
    {
        if ($this->projectId) {
            $this->checkPermissionOrFail('projets.modifier');

            // 2. Vérification d'accès avant modification
            $project = Project::findOrFail($this->projectId);
            $this->validateAccess($project);

            // Normalisation avant validation
            $this->code = $this->normalizeCode($this->code);
            $this->description = $this->sanitizeInput($this->description);
            $this->validate();

            $project = Project::findOrFail($this->projectId);

            $project->update([
                'code' => trim($this->code),
                'name' => trim($this->name),
                'description' => trim($this->description) ?: null,
                'manager_id' => $this->manager_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
            ]);

            session()->flash('success', 'Projet mis à jour avec succès.');
        } else {
            $this->checkPermissionOrFail('projets.creer');
            $this->validate();

            Project::create([
                'code' => trim($this->code),
                'name' => trim($this->name),
                'description' => trim($this->description) ?: null,
                'manager_id' => $this->manager_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
            ]);

            session()->flash('success', 'Projet créé avec succès.');
        }

        $this->closeModal();
    }

    protected function sanitizeInput($input): ?string
    {
        if (empty($input)) {
            return null;
        }

        // 2. Suppression des tags HTML
        $clean = strip_tags($input, '<p><br><strong><em><ul><ol><li>'); // Tags autorisés

        // 3. Échappement des caractères spéciaux
        $clean = htmlspecialchars($clean, ENT_QUOTES, 'UTF-8');

        // 4. Limitation de la longueur
        $clean = substr($clean, 0, 1000);

        // 5. Protection contre les injections
        $clean = preg_replace('/javascript:/i', '', $clean);
        $clean = preg_replace('/on\w+=/i', '', $clean);

        return trim($clean);
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail('projets.supprimer');

        // Validation supplémentaire pour la suppression
        if (! Gate::allows('projets.supprimer', Project::find($id))) {
            throw ValidationException::withMessages([
                'permission' => ["Vous n'avez pas les droits pour supprimer ce projet."],
            ]);
        }

        $project = Project::findOrFail($id);
        $this->deleteId = $project->id;
        $this->deleteName = $project->name;
        $this->showDeleteModal = true;
        $this->showModal = false;
    }

    public function delete()
    {
        $this->checkPermissionOrFail('projets.supprimer');

        if (! $this->deleteId) {
            throw ValidationException::withMessages([
                'permission' => ['Aucun projet sélectionné.'],
            ]);
        }

        // 1. Récupération avec verrouillage
        $project = Project::withCount(['subProjects', 'activities'])
            ->findOrFail($this->deleteId);

        // 2. Vérification des dépendances
        $dependencies = [];

        if ($project->sub_projects_count > 0) {
            $dependencies[] = "{$project->sub_projects_count} sous-projet(s)";
        }

        if ($project->activities_count > 0) {
            $dependencies[] = "{$project->activities_count} activité(s)";
        }

        // 3. Si des dépendances existent, demander confirmation
        if (! empty($dependencies)) {
            session()->flash('warning', 'Ce projet est lié à '.implode(' et ', $dependencies).
                '. La suppression est irréversible.');

            // 4. Demander confirmation supplémentaire
            $this->dispatch('confirm-delete-with-dependencies');

            return;
        }

        // 5. Suppression avec transaction
        DB::transaction(function () use ($project) {
            // Journaliser avant suppression
            activity()
                ->performedOn($project)
                ->causedBy(Auth::id())
                ->withProperties([
                    'code' => $project->code,
                    'name' => $project->name,
                    'deleted_at' => now(),
                ])
                ->log('Projet supprimé');

            $project->delete();
        });

        session()->flash('success', 'Le projet a été supprimé définitivement.');
        $this->closeDeleteModal();
    }

    protected function validateAccess(Project $project): void
    {
        // 3. Vérification des droits spécifiques
        if (Gate::allows('admin')) {
            return; // Admin peut tout faire
        }

        $userId = Auth::id();

        // 4. Vérification du rôle
        if ($project->manager_id !== $userId) {
            throw ValidationException::withMessages([
                'permission' => ["Vous n'êtes pas le responsable de ce projet."],
            ]);
        }

        // 5. Vérification des droits d'équipe
        if (! Gate::allows('projets.modifier', $project)) {
            throw ValidationException::withMessages([
                'permission' => ["Vous n'avez pas les droits sur ce projet."],
            ]);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->showModal = false;
        $this->deleteId = null;
        $this->deleteName = null;
    }

    private function resetForm()
    {
        $this->reset(['code', 'name', 'description', 'manager_id', 'start_date', 'end_date', 'status', 'projectId']);
        $this->resetValidation();
        $this->resetPage();
    }
}
