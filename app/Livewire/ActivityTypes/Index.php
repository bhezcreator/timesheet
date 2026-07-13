<?php

namespace App\Livewire\ActivityTypes;

use App\Models\ActivityType;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Variables de formulaire sécurisées
    public string $name = '';
    public ?string $description = null;
    public string $color = '#3B82F6'; // Bleu par défaut (Tailwind blue-500)
    public bool $is_active = true;
    public ?int $activityTypeId = null;
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
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:activity_types,name,' . $this->activityTypeId,
                'regex:/^[a-z0-9\-\._ ]+$/i'
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'], // Validation code HEX
            'is_active' => ['boolean'],
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

        $activityTypes = ActivityType::query()
            ->withCount('activities') // Optimisation pour afficher le nombre d'activités liées
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm);
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.activity-types.index', [
            'activityTypes' => $activityTypes,
        ]);
    }

    public function openModal()
    {
        $this->checkPermissionOrFail("types_activites.creer");
        $this->resetForm();
        $this->isOpen = true;
        $this->dispatch('open-modal', id: 'activity-type-modal');
    }

    public function edit($id)
    {
        $this->checkPermissionOrFail("types_activites.modifier");

        $type = ActivityType::findOrFail($id);
        $this->activityTypeId = $type->id;
        $this->name = $type->name;
        $this->description = $type->description;
        $this->color = $type->color;
        $this->is_active = $type->is_active;
        $this->isOpen = true;

        $this->dispatch('open-modal', id: 'activity-type-modal');
    }

    public function save()
    {
        $this->validate();

        if ($this->activityTypeId) {
            $this->checkPermissionOrFail("types_activites.modifier");

            $type = ActivityType::findOrFail($this->activityTypeId);
            $type->update([
                'name' => trim($this->name),
                'description' => trim($this->description),
                'color' => $this->color,
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Type d\'activité modifié avec succès.');
        } else {
            $this->checkPermissionOrFail("types_activites.creer");

            ActivityType::create([
                'name' => trim($this->name),
                'description' => trim($this->description),
                'color' => $this->color,
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Type d\'activité créé avec succès.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail("types_activites.supprimer");

        $type = ActivityType::findOrFail($id);

        // Sécurité additionnelle : empêcher la suppression si des activités l'utilisent encore
        if ($type->activities()->exists()) {
            throw ValidationException::withMessages([
                'activity_type' => ["Action impossible : Ce type est associé à des activités existantes."]
            ]);
        }

        $this->deleteId = $type->id;
        $this->deleteName = $type->name;

        $this->dispatch('open-modal', id: 'delete-activity-type-modal');
    }

    public function delete(int $id)
    {
        $this->checkPermissionOrFail("types_activites.supprimer");

        if ($this->deleteId === $id) {
            $type = ActivityType::findOrFail($id);
            $type->delete();
            session()->flash('success', 'Type d\'activité supprimé avec succès.');
        }

        $this->deleteId = null;
        $this->deleteName = null;
        $this->dispatch('close-modal', id: 'delete-activity-type-modal');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
        $this->dispatch('close-modal', 'activity-type-modal');
    }

    private function resetForm()
    {
        $this->reset(['name', 'description', 'color', 'is_active', 'activityTypeId']);
        $this->color = '#3B82F6';
        $this->is_active = true;
        $this->resetValidation();
    }
}
