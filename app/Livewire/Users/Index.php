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

    public string $password = '';

    public bool $is_active = true;

    public array $selectedRoles = [];

    public ?int $userId = null;

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
            'num_order' => ['required', 'string', 'max:50', 'unique:users,num_order,' . $this->userId],
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['required', 'string', 'max:255'],
            'supervisor_id' => ['nullable', 'integer', 'exists:users,id',   function ($attribute, $value, $fail) {
                // Vérifier si l'utilisateur a le rôle "manager"
                $user = User::find($value);
                if ($user && !$user->hasRole('Superviseur')) {
                    $fail('L\'utilisateur sélectionné doit avoir le rôle de superviseur.');
                }
            }],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->userId],
            'is_active' => ['required', 'boolean'],
            'selectedRoles' => [
                'required',
                'array',
                'min:1',
                // 1. Validation de chaque rôle
                function ($attribute, $value, $fail) {
                    $roles = Role::whereIn('id', $value)->get();

                    // 2. Vérification que tous les rôles existent
                    if ($roles->count() !== count($value)) {
                        $fail('Un ou plusieurs rôles sont invalides.');
                    }
                },
            ],
            'password' => [
                $this->userId ? 'nullable' : 'required',
                'string',
                'min:8',
                // 3. Validation de la force du mot de passe
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ],
        ];
    }

    /**
     * Messages d'erreur personnalisés et compréhensibles.
     */
    protected function messages(): array
    {
        return [
            // Messages génériques réutilisés par Laravel (:attribute sera remplacé par le nom lisible)
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit être du texte.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'unique' => 'Cette valeur est déjà utilisée par un autre utilisateur.',
            'email' => 'L’adresse email saisie n’est pas valide.',
            'exists' => 'Le responsable sélectionné n’existe pas.',

            // Messages spécifiques
            'selectedRoles.required' => 'Vous devez attribuer au moins un rôle à cet utilisateur.',
            'selectedRoles.min' => 'Vous devez sélectionner au moins :min rôle.',

            'password.required' => 'Le mot de passe est obligatoire pour un nouvel utilisateur.',
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial (@, $, !, %, *, ?, &).',
        ];
    }

    /**
     * Traduction des noms des champs informatiques en français lisible.
     */
    protected function validationAttributes(): array
    {
        return [
            'num_order' => 'numéro d’ordre',
            'name' => 'nom complet',
            'first_name' => 'prénom',
            'last_name' => 'nom de famille',
            'job_title' => 'titre du poste',
            'supervisor_id' => 'responsable hiérarchique',
            'email' => 'adresse email',
            'is_active' => 'statut d’activation',
            'selectedRoles' => 'rôles',
            'password' => 'mot de passe',
        ];
    }

    public function mount()
    {
        // 1. Nettoyer le mot de passe en mémoire
        $this->password = '';
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
        // 1. Nettoyage et validation de la recherche
        $searchTerm = trim($this->search);
        $searchTerm = preg_replace('/[^\p{L}\p{N}\s\-_.@]/u', '', $searchTerm);

        // 2. Construction sécurisée avec bindings
        $users = User::query()
            ->with(['supervisor', 'roles'])
            ->when(! empty($searchTerm), function ($query) use ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('first_name', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('num_order', 'LIKE', '%' . $searchTerm . '%');
                });
            })
            ->latest()
            ->paginate(10);

        // 4. Sécurisation des superviseurs
        $supervisors = User::query()
            ->where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Superviseur');
            })
            ->when($this->userId, fn($q) => $q->where('id', '!=', $this->userId))
            ->orderBy('name')
            ->limit(100)
            ->get();

        $allRoles = Role::query()
            ->orderBy('name')
            ->get();

        return view('livewire.users.index', [
            'users' => $users,
            'supervisors' => $supervisors,
            'allRoles' => $allRoles,
        ]);
    }

    public function openModal()
    {
        $this->checkPermissionOrFail('utilisateurs.creer');
        $this->resetForm();
        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    public function edit($id)
    {
        $this->checkPermissionOrFail('utilisateurs.modifier');

        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->num_order = $user->num_order;
        $this->name = $user->name;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name ?? '';
        $this->job_title = $user->job_title;
        $this->supervisor_id = $user->supervisor_id;
        $this->email = $user->email;
        $this->is_active = (bool) $user->is_active;
        $this->password = '';

        $this->selectedRoles = $user->roles->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    public function save()
    {
        if ($this->userId) {
            $this->checkPermissionOrFail('utilisateurs.modifier');
            $this->validate();

            // Vérification des cycles de supervision
            if ($this->supervisor_id) {
                $this->validateSupervisorCycle($this->supervisor_id);
            }

            $user = User::findOrFail($this->userId);

            $data = [
                'num_order' => trim($this->num_order),
                'name' => trim($this->name),
                'first_name' => trim($this->first_name),
                'last_name' => trim($this->last_name) ?: null,
                'job_title' => trim($this->job_title),
                'supervisor_id' => $this->supervisor_id,
                'email' => trim($this->email),
                'is_active' => $this->is_active,
            ];

            if (! empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            $user->update($data);

            // Synchronisation sécurisée des rôles
            $roleNames = Role::whereIn('id', $this->selectedRoles)
                ->where('guard_name', 'web') // Filtrer par guard
                ->pluck('name')
                ->toArray();

            $user->syncRoles($roleNames);

            session()->flash('success', 'Personnel et habilitations mis à jour avec succès.');
        } else {
            $this->checkPermissionOrFail('utilisateurs.creer');
            $this->validate();

            // Vérification des cycles de supervision
            if ($this->supervisor_id) {
                $this->validateSupervisorCycle($this->supervisor_id);
            }

            $user = User::create([
                'num_order' => trim($this->num_order),
                'name' => trim($this->name),
                'first_name' => trim($this->first_name),
                'last_name' => trim($this->last_name) ?: null,
                'job_title' => trim($this->job_title),
                'supervisor_id' => $this->supervisor_id,
                'email' => trim($this->email),
                'password' => Hash::make($this->password),
                'is_active' => $this->is_active,
                'settings' => [
                    'notifications' => [
                        'database' => true,
                        'email' => false,
                    ],
                ],
            ]);

            // Synchronisation sécurisée des rôles
            $roleNames = Role::whereIn('id', $this->selectedRoles)
                ->where('guard_name', 'web') // Filtrer par guard
                ->pluck('name')
                ->toArray();

            $user->syncRoles($roleNames);

            session()->flash('success', 'Personnel créé et affecté avec succès.');
        }

        $this->closeModal();
    }

    protected function validateSupervisorCycle(int $supervisorId): void
    {
        // Empêcher un utilisateur d'être son propre superviseur
        if ($this->userId && $supervisorId === $this->userId) {
            throw ValidationException::withMessages([
                'supervisor_id' => ['Un utilisateur ne peut pas être son propre superviseur.'],
            ]);
        }

        // Vérifier les cycles de supervision (boucles)
        $visited = [];
        $currentId = $supervisorId;
        $userId = $this->userId ?? Auth::id();

        while ($currentId && ! in_array($currentId, $visited)) {
            if ($currentId === $userId) {
                throw ValidationException::withMessages([
                    'supervisor_id' => ['Cette configuration créerait une boucle de supervision.'],
                ]);
            }

            $visited[] = $currentId;
            $supervisor = User::find($currentId);
            $currentId = $supervisor?->supervisor_id;
        }
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail('utilisateurs.supprimer');

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            throw ValidationException::withMessages([
                'user' => ['Action impossible : Vous ne pouvez pas supprimer votre propre compte.'],
            ]);
        }

        $this->deleteId = $user->id;
        $this->deleteName = $user->first_name . ' ' . $user->name;
        $this->showDeleteModal = true;
        $this->showModal = false;
    }

    public function delete()
    {
        $this->checkPermissionOrFail('utilisateurs.supprimer');

        if ($this->deleteId) {
            $user = User::findOrFail($this->deleteId);
            $user->delete();
            session()->flash('success', 'Compte Personnel supprimé définitivement.');
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
            'userId',
        ]);
        $this->resetValidation();
        $this->resetPage();
    }
}
