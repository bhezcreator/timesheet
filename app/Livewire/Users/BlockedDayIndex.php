<?php

namespace App\Livewire\Users;

use App\Models\BlockedDay;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class BlockedDayIndex extends Component
{
    use WithPagination;

    // Variables de formulaire sécurisées
    public string $date = '';
    public string $name = '';
    public string $type = 'Jour férié';
    public bool $is_active = true;
    public ?int $blockedDayId = null;
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

    protected function rules()
    {
        return [
            'date' => ['required', 'date', 'unique:blocked_days,date,' . $this->blockedDayId],
            'name' => [
                'required',
                'string',
                'max:255',
                "regex:/^[a-z0-9\-\._ a-z0-9àâäéèêëîïôöùûüç'&(),;.ÂÆÇÈÉÊËÎÏÔŒÙÛÜ]+$/i"
            ],
            'type' => [
                'required',
                'in:Jour férié,Fête religieuse,Congé entreprise,Pont entreprise,Jour chômé,Événement interne,Urgence / Force majeure,Maintenance,Autre'
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Valide une permission et lève une erreur propre interceptée par le Front-End.
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

        $blockedDays = BlockedDay::query()
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('type', 'like', $searchTerm);
                });
            })
            ->orderBy('date', 'desc')
            ->paginate(10);

        // Types disponibles pour alimenter le sélecteur moderne du Front-End
        $availableTypes = [
            'Jour férié',
            'Fête religieuse',
            'Congé entreprise',
            'Pont entreprise',
            'Jour chômé',
            'Événement interne',
            'Urgence / Force majeure',
            'Maintenance',
            'Autre'
        ];

        return view('livewire.users.blocked-day-index', [
            'blockedDays' => $blockedDays,
            'availableTypes' => $availableTypes,
        ]);
    }

    public function openModal()
    {
        $this->checkPermissionOrFail("jour_bloque.creer");
        $this->resetForm();
        $this->isOpen = true;
        $this->dispatch('open-modal', id: 'blocked-day-modal');
    }

    public function edit($id)
    {
        $this->checkPermissionOrFail("jour_bloque.modifier");

        $blockedDay = BlockedDay::findOrFail($id);
        $this->blockedDayId = $blockedDay->id;
        $this->date = $blockedDay->date ? $blockedDay->date->format('Y-m-d') : '';
        $this->name = $blockedDay->name;
        $this->type = $blockedDay->type;
        $this->is_active = (bool)$blockedDay->is_active;
        $this->isOpen = true;

        $this->dispatch('open-modal', id: 'blocked-day-modal');
    }

    public function save()
    {
        $this->validate();

        if ($this->blockedDayId) {
            $this->checkPermissionOrFail("jour_bloque.modifier");

            $blockedDay = BlockedDay::findOrFail($this->blockedDayId);
            $blockedDay->update([
                'date' => $this->date,
                'name' => trim($this->name),
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Jour bloqué modifié avec succès.');
        } else {
            $this->checkPermissionOrFail("jour_bloque.creer");

            BlockedDay::create([
                'date' => $this->date,
                'name' => trim($this->name),
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Jour bloqué enregistré avec succès.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail("jour_bloque.supprimer");

        $blockedDay = BlockedDay::findOrFail($id);
        $this->deleteId = $blockedDay->id;
        $this->deleteName = $blockedDay->name . ' (' . ($blockedDay->date ? $blockedDay->date->format('d/m/Y') : '') . ')';

        $this->dispatch('open-modal', id: 'delete-blocked-day-modal');
    }

    public function delete(int $id)
    {
        $this->checkPermissionOrFail("jour_bloque.supprimer");

        if ($this->deleteId === $id) {
            $blockedDay = BlockedDay::findOrFail($id);
            $blockedDay->delete();
            session()->flash('success', 'Jour bloqué supprimé avec succès.');
        }

        $this->deleteId = null;
        $this->deleteName = null;
        $this->dispatch('close-modal', id: 'delete-blocked-day-modal');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
        $this->dispatch('close-modal', 'blocked-day-modal');
    }

    private function resetForm()
    {
        $this->reset(['date', 'name', 'type', 'is_active', 'blockedDayId']);
        $this->type = 'Jour férié';
        $this->is_active = true;
        $this->resetValidation();
    }
}
