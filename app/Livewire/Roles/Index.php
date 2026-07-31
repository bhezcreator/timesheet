<?php

namespace App\Livewire\Roles;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Variables de formulaire
    public string $name = '';
    public array $selectedPermissions = [];
    public ?int $roleId = null;

    // Gestion des Modales
    public bool $showModal = false;
    public bool $showDeleteModal = false;

    // Variables de suppression
    public ?int $deleteId = null;
    public ?string $deleteName = null;

    // Filtre de recherche
    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    // Règles de validation
    protected $rules = [
        'name' => ['required', 'string', 'max:255', 'unique:roles,name', 'regex:/^[a-z0-9\-\._ ]+$/i'],
    ];

    // Gestion des permissions
    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ["Action non autorisée : Privilèges insuffisants pour exécuter cette opération."]
        ]);
    }

    // Rendu principal
    public function render()
    {
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $this->search) . '%';

        $roles = Role::query()
            ->with('permissions')
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm);
            })
            ->latest()
            ->paginate(10);

        $allPermissions = Permission::query()->orderBy('name')->get();

        return view('livewire.roles.index', [
            'roles' => $roles,
            'allPermissions' => $allPermissions
        ]);
    }

    // Ouvrir la modale de création
    public function openModal()
    {
        $this->checkPermissionOrFail("roles.creer");
        $this->resetForm();
        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    // Ouvrir la modale d'édition
    public function edit($id)
    {
        $this->checkPermissionOrFail("roles.modifier");

        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    // Sauvegarder (création ou modification)
    public function save()
    {
        if ($this->roleId) {
            $this->checkPermissionOrFail("roles.modifier");
            $this->validate([
                'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $this->roleId]
            ]);

            $role = Role::findOrFail($this->roleId);
            $role->update(['name' => trim($this->name)]);

            $permissionNames = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();
            $role->syncPermissions($permissionNames);

            session()->flash('success', 'Rôle modifié et permissions synchronisées avec succès.');
        } else {
            $this->checkPermissionOrFail("roles.creer");
            $this->validate();

            $role = Role::create([
                'name' => trim($this->name),
                'guard_name' => 'web'
            ]);

            $permissionNames = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();
            $role->syncPermissions($permissionNames);

            session()->flash('success', 'Rôle créé avec succès.');
        }

        $this->closeModal();
    }

    // Confirmer la suppression
    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail("roles.supprimer");

        $role = Role::findOrFail($id);
        if ($role->name === 'Admin') {
            throw ValidationException::withMessages([
                'role' => ["Action impossible : Le rôle de sécurité [super-admin] ne peut pas être supprimé."]
            ]);
        }

        $this->deleteId = $role->id;
        $this->deleteName = $role->name;
        $this->showDeleteModal = true;
        $this->showModal = false;
    }

    // Exécuter la suppression
    public function delete()
    {
        $this->checkPermissionOrFail("roles.supprimer");

        if ($this->deleteId) {
            $role = Role::findOrFail($this->deleteId);
            $role->delete();
            session()->flash('success', 'Rôle supprimé avec succès.');
        }

        $this->closeDeleteModal();
    }

    // Fermer toutes les modales
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    // Fermer la modale de suppression
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->showModal = false;
        $this->deleteId = null;
        $this->deleteName = null;
    }

    // 🔄 Réinitialiser le formulaire
    private function resetForm()
    {
        $this->reset(['name', 'roleId', 'selectedPermissions']);
        $this->resetValidation();
    }

    // 🔍 Gestion de la recherche
    public function updatingSearch()
    {
        $this->resetPage();
    }
}
