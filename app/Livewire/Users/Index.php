<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Champs de formulaire indexés sur le modèle
    public string $num_order = '';
    public string $name = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $job_title = '';
    public ?int $supervisor_id = null;
    public string $email = '';
    public string $password = ''; // Géré séparément pour édition optionnelle
    public bool $is_active = true;
    public array $selectedRoles = []; // Liste des noms/IDs des rôles cochés

    public ?int $userId = null;
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
            'num_order'   => ['required', 'string', 'max:50', 'unique:users,num_order,' . $this->userId],
            'name'        => ['required', 'string', 'max:255'],
            'first_name'  => ['required', 'string', 'max:255'],
            'last_name'   => ['nullable', 'string', 'max:255'],
            'job_title'   => ['required', 'string', 'max:255'],
            'supervisor_id' => ['nullable', 'integer', 'exists:users,id'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->userId],
            'password'    => $this->userId ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'],
            'is_active'   => ['required', 'boolean'],
            'selectedRoles' => ['required', 'array', 'min:1'],
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

        $users = User::query()
            ->with(['supervisor', 'roles']) // Eager loading anti-requêtes N+1
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('first_name', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('num_order', 'like', $searchTerm);
            })
            ->latest()
            ->paginate(10);

        // Récupération des données pour alimenter les listes déroulantes du formulaire
        $supervisors = User::query()
            ->where('is_active', true)
            ->when($this->userId, fn($q) => $q->where('id', '!=', $this->userId)) // Éviter l'auto-supervision
            ->orderBy('name')
            ->get();

        $allRoles = Role::query()->orderBy('name')->get();

        return view('livewire.users.index', [
            'users' => $users,
            'supervisors' => $supervisors,
            'allRoles' => $allRoles,
        ]);
    }

    public function openModal()
    {
        $this->checkPermissionOrFail("utilisateurs.creer");
        $this->resetForm();
        $this->isOpen = true;
        $this->dispatch('open-modal', id: 'user-modal');
    }

    public function edit($id)
    {
        $this->checkPermissionOrFail("utilisateurs.modifier");

        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->num_order = $user->num_order;
        $this->name = $user->name;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name ?? '';
        $this->job_title = $user->job_title;
        $this->supervisor_id = $user->supervisor_id;
        $this->email = $user->email;
        $this->is_active = (bool)$user->is_active;
        $this->password = ''; // Toujours vide à l'affichage par sécurité

        // Extraction des IDs des rôles associés pour alimenter les cases à cocher du Front-End
        $this->selectedRoles = $user->roles->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->isOpen = true;

        $this->dispatch('open-modal', id: 'user-modal');
    }

    public function save()
    {
        if ($this->userId) {
            $this->checkPermissionOrFail("utilisateurs.modifier");
            $this->validate();

            $user = User::findOrFail($this->userId);

            $data = [
                'num_order'   => trim($this->num_order),
                'name'        => trim($this->name),
                'first_name'  => trim($this->first_name),
                'last_name'   => trim($this->last_name) ?: null,
                'job_title'   => trim($this->job_title),
                'supervisor_id' => $this->supervisor_id,
                'email'       => trim($this->email),
                'is_active'   => $this->is_active,
            ];

            // Hashage du mot de passe uniquement s'il a été rempli dans le formulaire d'édition
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            $user->update($data);

            // Synchronisation sécurisée Spatie via correspondance ID -> Nom
            $roleNames = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();
            $user->syncRoles($roleNames);

            session()->flash('success', 'Personnel et habilitations mis à jour avec succès.');
        } else {
            $this->checkPermissionOrFail("utilisateurs.creer");
            $this->validate();

            $user = User::create([
                'num_order'   => trim($this->num_order),
                'name'        => trim($this->name),
                'first_name'  => trim($this->first_name),
                'last_name'   => trim($this->last_name) ?: null,
                'job_title'   => trim($this->job_title),
                'supervisor_id' => $this->supervisor_id,
                'email'       => trim($this->email),
                'password'    => Hash::make($this->password),
                'is_active'   => $this->is_active,
            ]);

            $roleNames = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();
            $user->syncRoles($roleNames);

            session()->flash('success', 'Personnel créé et affecté avec succès.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail("utilisateurs.supprimer");

        $user = User::findOrFail($id);

        // Empêcher l'auto-suppression accidentelle ou malveillante
        if ($user->id === Auth::id()) {
            throw ValidationException::withMessages([
                'user' => ["Action impossible : Vous ne pouvez pas supprimer votre propre compte."]
            ]);
        }

        $this->deleteId = $user->id;
        $this->deleteName = $user->first_name . ' ' . $user->name;

        $this->dispatch('open-modal', id: 'delete-user-modal');
    }

    public function delete(int $id)
    {
        $this->checkPermissionOrFail("utilisateurs.supprimer");

        if ($this->deleteId === $id) {
            $user = User::findOrFail($id);
            $user->delete();
            session()->flash('success', 'Compte Personnel supprimé définitivement.');
        }

        $this->deleteId = null;
        $this->deleteName = null;
        $this->dispatch('close-modal', id: 'delete-user-modal');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
        $this->dispatch('close-modal', 'user-modal');
    }

    private function resetForm()
    {
        $this->reset([
            'num_order',
            'name',
            'first_name',
            'last_name',
            'job_title',
            'supervisor_id',
            'email',
            'password',
            'is_active',
            'selectedRoles',
            'userId'
        ]);
        $this->resetValidation();
        $this->resetPage();
    }
}
