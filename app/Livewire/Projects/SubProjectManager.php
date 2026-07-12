<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SubProjectManager extends Component
{
    use WithPagination;

    // Projet parent
    public int $projectId;
    public Project $project;

    // Champs du formulaire indexés sur le modèle SubProject
    public string $name = '';
    public string $description = '';
    public string $status = 'brouillon';

    public ?int $subProjectId = null;
    public bool $isOpen = false;

    // Variables de suppression sécurisée
    public ?int $deleteId = null;
    public ?string $deleteName = null;

    // Recherche réactive
    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(int $projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::findOrFail($projectId);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'status'        => ['required', 'string', 'in:brouillon,actif,annuler'],
        ];
    }

    /**
     * Valide l'accès et lève une exception de validation standard interceptée par l'interface.
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

    public function render()
    {
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $this->search) . '%';

        $subProjects = SubProject::query()
            ->with('users') // Évite le problème des requêtes N+1
            ->where('project_id', $this->projectId)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('status', 'like', $searchTerm);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.projects.sub-project-manager', [
            'subProjects' => $subProjects,
        ]);
    }

    public function openModal()
    {
        $this->checkPermissionOrFail("projets.creer");
        $this->resetForm();
        $this->isOpen = true;
        $this->dispatch('open-modal', id: 'sub-project-modal');
    }

    public function edit($id)
    {
        $this->checkPermissionOrFail("projets.modifier");

        // Sécurité renforcée : On s'assure que le sous-projet appartient bien au projet chargé
        $subProject = SubProject::where('project_id', $this->projectId)->findOrFail($id);

        $this->subProjectId = $subProject->id;
        $this->name = $subProject->name;
        $this->description = $subProject->description ?? '';
        $this->status = $subProject->status;

        $this->isOpen = true;
        $this->dispatch('open-modal', id: 'sub-project-modal');
    }

    public function save()
    {
        if ($this->subProjectId) {
            $this->checkPermissionOrFail("projets.modifier");
            $this->validate();

            $subProject = SubProject::where('project_id', $this->projectId)->findOrFail($this->subProjectId);

            $subProject->update([
                'name'        => trim($this->name),
                'description' => trim($this->description) ?: null,
                'status'      => $this->status,
            ]);

            session()->flash('success', 'Sous-projet mis à jour avec succès.');
        } else {
            $this->checkPermissionOrFail("projets.creer");
            $this->validate();

            $subProject = SubProject::create([
                'project_id'  => $this->projectId,
                'name'        => trim($this->name),
                'description' => trim($this->description) ?: null,
                'status'      => $this->status,
            ]);

            session()->flash('success', 'Sous-projet créé avec succès.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail("projets.supprimer");

        $subProject = SubProject::where('project_id', $this->projectId)->findOrFail($id);
        $this->deleteId = $subProject->id;
        $this->deleteName = $subProject->name;

        $this->dispatch('open-modal', id: 'delete-sub-project-modal');
    }

    public function delete(int $id)
    {
        $this->checkPermissionOrFail("projets.supprimer");

        if ($this->deleteId === $id) {
            $subProject = SubProject::where('project_id', $this->projectId)->findOrFail($id);

            // Le cascadeOnDelete() en BDD nettoiera sub_project_user, mais Eloquent le gère par précaution
            $subProject->users()->detach();
            $subProject->delete();

            session()->flash('success', 'Le sous-projet a été supprimé définitivement.');
        }

        $this->deleteId = null;
        $this->deleteName = null;
        $this->dispatch('close-modal', id: 'delete-sub-project-modal');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
        $this->dispatch('close-modal', 'sub-project-modal');
    }

    private function resetForm()
    {
        $this->reset(['name', 'description', 'status']);
        $this->resetValidation();
        $this->resetPage();
    }
}
