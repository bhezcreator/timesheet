<?php

namespace App\Livewire\Settings\Offices;

use App\Models\Office;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Variables de formulaire
    public string $name = '';
    public string $code = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?int $radius = 100;
    public string $address = '';
    public bool $is_active = true;

    public ?int $officeId = null;

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
    protected function rules()
    {
        $uniqueRule = $this->officeId 
            ? 'unique:offices,code,' . $this->officeId 
            : 'unique:offices,code';

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', $uniqueRule, 'regex:/^[A-Z0-9\-_]+$/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'integer', 'min:10', 'max:10000'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    // Messages de validation personnalisés
    protected function messages()
    {
        return [
            'code.regex' => 'Le code doit contenir uniquement des lettres majuscules, des chiffres, des tirets et des underscores.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90 degrés.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180 degrés.',
            'radius.min' => 'Le rayon doit être d\'au moins 10 mètres.',
            'radius.max' => 'Le rayon ne peut pas dépasser 10 000 mètres.',
        ];
    }

    // Gestion des permissions
    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ['Action non autorisée : Privilèges insuffisants pour exécuter cette opération.'],
        ]);
    }

    // Rendu principal
    public function render()
    {
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $this->search) . '%';

        $offices = Office::query()
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('code', 'like', $searchTerm)
                        ->orWhere('address', 'like', $searchTerm);
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.settings.offices.index', [
            'offices' => $offices,
        ]);
    }

    // Ouvrir la modale de création
    public function openModal()
    {
        $this->checkPermissionOrFail('offices.creer');
        $this->resetForm();
        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    // Ouvrir la modale d'édition
    public function edit($id)
    {
        $this->checkPermissionOrFail('offices.modifier');

        $office = Office::findOrFail($id);
        $this->officeId = $office->id;
        $this->name = $office->name;
        $this->code = $office->code;
        $this->latitude = $office->latitude;
        $this->longitude = $office->longitude;
        $this->radius = $office->radius ?? 100;
        $this->address = $office->address;
        $this->is_active = $office->is_active;

        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    // Sauvegarder (création ou modification)
    public function save()
    {
        if ($this->officeId) {
            $this->checkPermissionOrFail('offices.modifier');
            $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:50', 'unique:offices,code,' . $this->officeId, 'regex:/^[A-Z0-9\-_]+$/'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'radius' => ['nullable', 'integer', 'min:10', 'max:10000'],
                'address' => ['nullable', 'string', 'max:500'],
                'is_active' => ['boolean'],
            ]);

            $office = Office::findOrFail($this->officeId);
            $office->update([
                'name' => trim($this->name),
                'code' => trim($this->code),
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'radius' => $this->radius,
                'address' => trim($this->address),
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Bureau modifié avec succès.');
        } else {
            $this->checkPermissionOrFail('offices.creer');
            $this->validate();

            Office::create([
                'name' => trim($this->name),
                'code' => trim($this->code),
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'radius' => $this->radius,
                'address' => trim($this->address),
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Bureau créé avec succès.');
        }

        $this->closeModal();
    }

    // Confirmer la suppression
    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail('offices.supprimer');

        $office = Office::findOrFail($id);
        
        // Vérifier si le bureau a des présences associées
        if ($office->attendanceRecords()->count() > 0) {
            throw ValidationException::withMessages([
                'office' => ['Impossible de supprimer ce bureau car il contient des enregistrements de présence associés.'],
            ]);
        }

        $this->deleteId = $office->id;
        $this->deleteName = $office->name;
        $this->showDeleteModal = true;
        $this->showModal = false;
    }

    // Exécuter la suppression
    public function delete()
    {
        $this->checkPermissionOrFail('offices.supprimer');

        if ($this->deleteId) {
            $office = Office::findOrFail($this->deleteId);
            $office->delete();
            session()->flash('success', 'Bureau supprimé avec succès.');
        }

        $this->closeDeleteModal();
    }

    // Basculer le statut actif/inactif
    public function toggleStatus($id)
    {
        $this->checkPermissionOrFail('offices.modifier');

        $office = Office::findOrFail($id);
        $office->update([
            'is_active' => !$office->is_active
        ]);

        session()->flash('success', 'Statut du bureau modifié avec succès.');
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

    // Réinitialiser le formulaire
    private function resetForm()
    {
        $this->reset([
            'name', 
            'code', 
            'latitude', 
            'longitude', 
            'radius', 
            'address', 
            'is_active',
            'officeId'
        ]);
        $this->resetValidation();
    }

    // Gestion de la recherche
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Obtenir le statut du bureau
    public function getStatusBadgeProperty()
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    // Obtenir la couleur du statut
    public function getStatusColorProperty()
    {
        return $this->is_active ? 'success' : 'danger';
    }
}