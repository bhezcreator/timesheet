<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\User;
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
    public string $status = 'draft';

    public ?int $projectId = null;
    public bool $isOpen = false;

    // Variables de suppression sécurisée
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
            'code'        => ['required', 'string', 'max:50', 'unique:projects,code,' . $this->projectId, 'regex:/^[a-z0-9\-\._]+$/i'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'manager_id'  => ['required', 'integer', 'exists:users,id'],
            'start_date'  => ['required', 'date'],
            'end_date'    => ['required', 'date', 'after_or_equal:start_date'],
            'status'      => ['required', 'string', 'in:draft,submitted,approved,rejected'],
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

        $projects = Project::query()
            ->with('manager') // Évite le problème des requêtes N+1
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('code', 'like', $searchTerm)
                    ->orWhere('status', 'like', $searchTerm);
            })
            ->latest()
            ->paginate(10);

        // Liste des gestionnaires actifs pour alimenter le sélecteur personnalisé
        $managers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.projects.index', [
            'projects' => $projects,
            'managers' => $managers,
        ]);
    }

    public function openModal()
    {
        $this->checkPermissionOrFail("projets.creer");
        $this->resetForm();
        $this->isOpen = true;
        $this->dispatch('open-modal', id: 'project-modal');
    }

    public function edit($id)
    {
        $this->checkPermissionOrFail("projets.modifier");

        $project = Project::findOrFail($id);
        $this->projectId = $project->id;
        $this->code = $project->code;
        $this->name = $project->name;
        $this->description = $project->description ?? '';
        $this->manager_id = $project->manager_id;
        $this->start_date = $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '';
        $this->end_date = $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '';
        $this->status = $project->status;

        $this->isOpen = true;
        $this->dispatch('open-modal', id: 'project-modal');
    }

    public function save()
    {
        if ($this->projectId) {
            $this->checkPermissionOrFail("projets.modifier");
            $this->validate();

            $project = Project::findOrFail($this->projectId);

            $project->update([
                'code'        => trim($this->code),
                'name'        => trim($this->name),
                'description' => trim($this->description) ?: null,
                'manager_id'  => $this->manager_id,
                'start_date'  => $this->start_date,
                'end_date'    => $this->end_date,
                'status'      => $this->status,
            ]);

            session()->flash('success', 'Projet mis à jour avec succès.');
        } else {
            $this->checkPermissionOrFail("projets.creer");
            $this->validate();

            Project::create([
                'code'        => trim($this->code),
                'name'        => trim($this->name),
                'description' => trim($this->description) ?: null,
                'manager_id'  => $this->manager_id,
                'start_date'  => $this->start_date,
                'end_date'    => $this->end_date,
                'status'      => $this->status,
            ]);

            session()->flash('success', 'Projet créé avec succès.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail("projets.supprimer");

        $project = Project::findOrFail($id);
        $this->deleteId = $project->id;
        $this->deleteName = $project->name;

        $this->dispatch('open-modal', id: 'delete-project-modal');
    }

    public function delete(int $id)
    {
        $this->checkPermissionOrFail("projets.supprimer");

        if ($this->deleteId === $id) {
            $project = Project::findOrFail($id);
            $project->delete();
            session()->flash('success', 'Le projet a été supprimé définitivement.');
        }

        $this->deleteId = null;
        $this->deleteName = null;
        $this->dispatch('close-modal', id: 'delete-project-modal');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
        $this->dispatch('close-modal', 'project-modal');
    }

    private function resetForm()
    {
        $this->reset(['code', 'name', 'description', 'manager_id', 'start_date', 'end_date', 'status', 'projectId']);
        $this->resetValidation();
        $this->resetPage();
    }
}
