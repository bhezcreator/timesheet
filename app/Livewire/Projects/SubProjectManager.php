<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\SubProject;
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:brouillon,actif,annuler'],
        ];
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

    public function render()
    {
        $searchTerm = '%'.str_replace(['%', '_'], ['\%', '\_'], $this->search).'%';

        $subProjects = SubProject::query()
            ->with('users')
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
        $this->checkPermissionOrFail('projets.creer');
        $this->resetForm();
        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    public function edit($id)
    {
        $this->checkPermissionOrFail('projets.modifier');

        $subProject = SubProject::where('project_id', $this->projectId)->findOrFail($id);

        $this->subProjectId = $subProject->id;
        $this->name = $subProject->name;
        $this->description = $subProject->description ?? '';
        $this->status = $subProject->status;

        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    public function save()
    {
        if ($this->subProjectId) {
            $this->checkPermissionOrFail('projets.modifier');
            $this->validate();

            $subProject = SubProject::where('project_id', $this->projectId)->findOrFail($this->subProjectId);

            $subProject->update([
                'name' => trim($this->name),
                'description' => trim($this->description) ?: null,
                'status' => $this->status,
            ]);

            session()->flash('success', 'Sous-projet mis à jour avec succès.');
        } else {
            $this->checkPermissionOrFail('projets.creer');
            $this->validate();

            SubProject::create([
                'project_id' => $this->projectId,
                'name' => trim($this->name),
                'description' => trim($this->description) ?: null,
                'status' => $this->status,
            ]);

            session()->flash('success', 'Sous-projet créé avec succès.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail('projets.supprimer');

        $subProject = SubProject::where('project_id', $this->projectId)->findOrFail($id);
        $this->deleteId = $subProject->id;
        $this->deleteName = $subProject->name;
        $this->showDeleteModal = true;
        $this->showModal = false;
    }

    public function delete()
    {
        $this->checkPermissionOrFail('projets.supprimer');

        if ($this->deleteId) {
            $subProject = SubProject::where('project_id', $this->projectId)->findOrFail($this->deleteId);
            $subProject->users()->detach();
            $subProject->delete();
            session()->flash('success', 'Le sous-projet a été supprimé définitivement.');
        }

        $this->closeDeleteModal();
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
        $this->reset(['name', 'description', 'status', 'subProjectId']);
        $this->resetValidation();
        $this->resetPage();
    }
}
