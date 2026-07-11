<?php

namespace App\Livewire\Permissions;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Livewire\Attributes\Layout;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Variables de formulaire sécurisées
    public string $name = '';
    public ?int $permissionId = null;
    public bool $isOpen = false;

    // Variables de suppression
    public ?int $deleteId = null;
    public ?string $deleteName = null;

    // Filtre de recherche nettoyé
    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected $rules = [
        'name' => ['required', 'string', 'max:255', 'unique:permissions,name', 'regex:/^[a-z0-9\-\._]+$/i'],
    ];

    /**
     * Valide une permission et lève une erreur propre interceptée par le Front-End.
     *
     * @param string $permission Le nom de la permission à tester
     * @throws ValidationException
     */
    protected function checkPermissionOrFail(string $permission): bool
    {
        // Gate::allows() utilise le système d'authentification et fonctionne de manière universelle
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ["Action non autorisée : Privilèges insuffisants."]
        ]);
    }


    public function render()
    {
        // Nettoyage préventif des caractères spéciaux pour éviter les bugs SQL/XSS
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $this->search) . '%';

        $permissions = Permission::query()
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.permissions.index', [
            'permissions' => $permissions,
        ]);
    }

    public function openModal()
    {
        $this->checkPermissionOrFail("manager-permission");
        $this->resetForm();
        $this->isOpen = true;
        $this->dispatch('open-modal', id: 'permission-modal');
    }

    public function edit($id)
    {
        $this->checkPermissionOrFail("manager-permission");

        $permission = Permission::findOrFail($id);
        $this->permissionId = $permission->id;
        $this->name = $permission->name;
        $this->isOpen = true;

        $this->dispatch('open-modal', id: 'permission-modal');
    }

    public function save()
    {
        $this->checkPermissionOrFail("manager-permission");

        if ($this->permissionId) {
            $this->validate([
                'name' => ['required', 'string', 'max:255', 'unique:permissions,name,' . $this->permissionId]
            ]);

            Permission::findOrFail($this->permissionId)->update([
                'name' => trim($this->name) // trim() évite les espaces inutiles accidentels
            ]);

            session()->flash('success', 'Permission modifiée avec succès.');
        } else {
            $this->validate();

            Permission::create([
                'name' => trim($this->name),
                'guard_name' => 'web'
            ]);

            session()->flash('success', 'Permission créée avec succès.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail("manager-permission");

        $permission = Permission::findOrFail($id);
        $this->deleteId = $permission->id;
        $this->deleteName = $permission->name;

        $this->dispatch('open-modal', id: 'delete-permission-modal');
    }

    // Sécurisation : On passe l'ID de confirmation directement à la méthode de destruction
    public function delete(int $id)
    {
        $this->checkPermissionOrFail("manager-permission");

        // Validation croisée : On vérifie que l'ID soumis correspond bien à la demande initiale
        if ($this->deleteId === $id) {
            Permission::findOrFail($id)->delete();
            session()->flash('success', 'Permission supprimée avec succès.');
        }

        $this->deleteId = null;
        $this->deleteName = null;
        $this->dispatch('close-modal', id: 'delete-permission-modal');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
        $this->dispatch('close-modal', 'permission-modal');
    }

    private function resetForm()
    {
        $this->reset(['name', 'permissionId']);
        $this->resetValidation();
    }
}
