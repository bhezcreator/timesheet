<?php

namespace App\Livewire\Activities;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UserActivities extends Component
{
    use WithPagination;

    // ID de l'utilisateur
    public int $userId;

    // Informations de l'utilisateur
    public ?User $user = null;

    // Variables de filtre
    public string $statusFilter = 'all'; // all, brouillon, soumis, approuvé, rejeté
    public string $search = '';
    public string $sortBy = 'activity_date';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    // Variables pour le modal de modification de statut
    public bool $showStatusModal = false;
    public ?int $selectedActivityId = null;
    public string $newStatus = '';
    public string $rejectionReason = '';

    // Variables pour les statistiques
    public array $stats = [];

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'activity_date'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // Règles de validation
    protected function rules()
    {
        $rules = [
            'newStatus' => ['required', 'in:approuvé,rejeté,brouillon,soumis'],
        ];

        if ($this->newStatus === 'rejeté') {
            $rules['rejectionReason'] = ['required', 'string', 'min:3', 'max:1000'];
        }

        return $rules;
    }

    protected $messages = [
        'newStatus.required' => 'Veuillez sélectionner un statut.',
        'newStatus.in' => 'Le statut sélectionné est invalide.',
        'rejectionReason.required' => 'Veuillez spécifier la raison du rejet.',
        'rejectionReason.min' => 'La raison du rejet doit contenir au moins 3 caractères.',
        'rejectionReason.max' => 'La raison du rejet ne peut pas dépasser 1000 caractères.',
    ];

    /**
     * Vérification des permissions
     */
    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ['Action non autorisée : Privilèges insuffisants pour exécuter cette opération.'],
        ]);
    }

    /**
     * Mount du composant
     */
    public function mount(int $userId)
    {
        $this->userId = $userId;
        $this->user = User::findOrFail($userId);
        $this->checkPermissionOrFail('activites.voir');
        $this->calculateStats();
    }

    /**
     * Calcule les statistiques des activités de l'utilisateur
     */
    public function calculateStats()
    {
        $query = Activity::where('user_id', $this->userId)
            ->whereMonth('activity_date', now()->month)
            ->whereYear('activity_date', now()->year);
            
        $this->stats = [
            'total' => $query->count(),
            'brouillon' => (clone $query)->where('status', 'brouillon')->count(),
            'soumis' => (clone $query)->where('status', 'soumis')->count(),
            'approuvé' => (clone $query)->where('status', 'approuvé')->count(),
            'rejeté' => (clone $query)->where('status', 'rejeté')->count(),
            'total_heures' => (clone $query)->sum('duration') ?? 0,
        ];
    }

    /**
     * Récupère les activités de l'utilisateur avec filtres
     */
    public function getActivitiesProperty()
    {
        $query = Activity::query()
            ->with(['project', 'subProject', 'activityType'])
            ->where('user_id', $this->userId)
            ->whereMonth('activity_date', now()->month)
            ->whereYear('activity_date', now()->year);

        // Filtre par statut
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Recherche
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('titre', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Tri
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    /**
     * Ouvrir le modal de modification de statut
     */
    public function openStatusModal(int $activityId)
    {
        $this->checkPermissionOrFail('activites.modifier');

        $activity = Activity::findOrFail($activityId);
        
        // Vérifier que l'activité appartient bien à l'utilisateur
        if ($activity->user_id !== $this->userId) {
            throw ValidationException::withMessages([
                'permission' => ['Cette activité n\'appartient pas à cet utilisateur.'],
            ]);
        }

        $this->selectedActivityId = $activityId;
        $this->newStatus = $activity->status;
        $this->rejectionReason = $activity->rejection_reason ?? '';
        $this->showStatusModal = true;
    }

    /**
     * Fermer le modal
     */
    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->selectedActivityId = null;
        $this->newStatus = '';
        $this->rejectionReason = '';
        $this->resetValidation();
    }

    /**
     * Mettre à jour le statut de l'activité
     */
    public function updateStatus()
    {
        $this->checkPermissionOrFail('activites.modifier');

        // Validation
        $this->validate();

        $activity = Activity::findOrFail($this->selectedActivityId);

        // Vérifier que l'activité appartient bien à l'utilisateur
        if ($activity->user_id !== $this->userId) {
            throw ValidationException::withMessages([
                'permission' => ['Cette activité n\'appartient pas à cet utilisateur.'],
            ]);
        }

        // Mettre à jour le statut
        $oldStatus = $activity->status;
        $activity->status = $this->newStatus;

        // Si rejeté, ajouter la raison
        if ($this->newStatus === 'rejeté') {
            $activity->rejection_reason = $this->rejectionReason;
        } else {
            $activity->rejection_reason = null;
        }

        // Si approuvé ou soumis, mettre à jour submitted_at
        if ($this->newStatus === 'approuvé' || $this->newStatus === 'soumis') {
            $activity->submitted_at = now();
        }

        $activity->save();

        // Message de succès
        session()->flash('success', "Statut de l'activité mis à jour avec succès.");

        // Rafraîchir les statistiques
        $this->calculateStats();

        // Fermer le modal
        $this->closeStatusModal();
    }

    /**
     * Réinitialiser les filtres
     */
    public function resetFilters()
    {
        $this->statusFilter = 'all';
        $this->search = '';
        $this->sortBy = 'activity_date';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    /**
     * Changer le tri
     */
    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Réinitialiser la pagination lors des recherches
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    /**
     * Rendu principal
     */
    public function render()
    {
        return view('livewire.activities.user-activities', [
            'activities' => $this->activities,
            'user' => $this->user,
            'stats' => $this->stats,
        ]);
    }
}