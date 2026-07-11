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

    // Variables de formulaire sécurisées
    public string $name = '';
    public array $selectedPermissions = []; // Stocke les IDs des permissions cochées
    public ?int $roleId = null;
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
        'name' => ['required', 'string', 'max:255', 'unique:roles,name', 'regex:/^[a-z0-9\-\._ ]+$/i'],
    ];

    /**
     * Valide une permission et lève une erreur propre interceptée par le Front-End.
     *
     * @param string $permission Le nom de la permission à tester
     * @throws ValidationException
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
        // Nettoyage préventif des caractères spéciaux pour éviter les bugs SQL/XSS
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $this->search) . '%';

        $roles = Role::query()
            ->with('permissions') // Optimisation des requêtes (Eager Loading)
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm);
            })
            ->latest()
            ->paginate(10);

        // Récupération de toutes les permissions disponibles pour l'attribution dans le formulaire
        $allPermissions = Permission::query()->orderBy('name')->get();

        return view('livewire.roles.index', [
            'roles' => $roles,
            'allPermissions' => $allPermissions
        ]);
    }

    public function openModal()
    {
        $this->checkPermissionOrFail("roles.creer");
        $this->resetForm();
        $this->isOpen = true;
        $this->dispatch('open-modal', id: 'role-modal');
    }

    public function edit($id)
    {
        $this->checkPermissionOrFail("roles.modifier");

        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;

        // On récupère les IDs des permissions associées à ce rôle sous forme de chaînes/entiers
        $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->isOpen = true;

        $this->dispatch('open-modal', id: 'role-modal');
    }

    public function save()
    {
        if ($this->roleId) {
            $this->checkPermissionOrFail("roles.modifier");

            $this->validate([
                'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $this->roleId]
            ]);

            $role = Role::findOrFail($this->roleId);
            $role->update([
                'name' => trim($this->name)
            ]);

            // --- CORRECTION : On convertit les IDs sélectionnés en noms de permissions ---
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

            // --- CORRECTION : Idem pour la création ---
            $permissionNames = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();
            $role->syncPermissions($permissionNames);

            session()->flash('success', 'Rôle créé avec succès.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail("roles.supprimer");

        // Sécurité : On empêche la suppression du rôle super-admin si critique pour l'application
        $role = Role::findOrFail($id);
        if ($role->name === 'Admin') {
            throw ValidationException::withMessages([
                'role' => ["Action impossible : Le rôle de sécurité [super-admin] ne peut pas être supprimé."]
            ]);
        }

        $this->deleteId = $role->id;
        $this->deleteName = $role->name;

        $this->dispatch('open-modal', id: 'delete-role-modal');
    }

    public function delete(int $id)
    {
        $this->checkPermissionOrFail("roles.supprimer");

        // Validation croisée : On vérifie que l'ID soumis correspond bien à la demande initiale
        if ($this->deleteId === $id) {
            $role = Role::findOrFail($id);

            // Spatie détache automatiquement les permissions liées avant suppression
            $role->delete();
            session()->flash('success', 'Rôle supprimé avec succès.');
        }

        $this->deleteId = null;
        $this->deleteName = null;
        $this->dispatch('close-modal', id: 'delete-role-modal');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
        $this->dispatch('close-modal', 'role-modal');
    }

    private function resetForm()
    {
        $this->reset(['name', 'roleId', 'selectedPermissions']);
        $this->resetValidation();
    }
}
