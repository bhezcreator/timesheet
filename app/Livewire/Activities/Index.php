<?php

namespace App\Livewire\Activities;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Filtres de recherche
    public string $search = '';
    public string $filterMonth = '';
    public string $filterYear = '';
    public string $filterStatus = '';

    // Variables de suppression
    public ?int $deleteId = null;
    public ?string $deleteName = null;

    // ID User
    public int $user_id;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterMonth' => ['except' => ''],
        'filterYear' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function mount()
    {
        $this->checkPermissionOrFail('activites.voir');
        $this->user_id = Auth::id();

        // Initialisation par défaut sur le mois et l'année en cours pour cibler l'affichage
        $this->filterMonth = json_encode(now()->month);
        $this->filterYear = json_encode(now()->year);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingFilterMonth()
    {
        $this->resetPage();
    }
    public function updatingFilterYear()
    {
        $this->resetPage();
    }
    public function updatingFilterStatus()
    {
        $this->resetPage();
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
        // Nettoyage préventif contre les bugs SQL / injections injectées
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $this->search) . '%';

        $activities = Activity::query()
            ->with(['project', 'subProject', 'activityType']) // Eager loading optimisé
            ->where('user_id', Auth::id())
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where('titre', 'like', $searchTerm);
            })
            ->when($this->filterMonth, function ($query) {
                $query->whereMonth('activity_date', $this->filterMonth);
            })
            ->when($this->filterYear, function ($query) {
                $query->whereYear('activity_date', $this->filterYear);
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->orderBy('activity_date', 'desc')
            ->latest()
            ->paginate(5);

        // Listes statiques pour alimenter les sélecteurs de filtres du tableau de bord
        $months = [
            '1' => 'Janvier',
            '2' => 'Février',
            '3' => 'Mars',
            '4' => 'Avril',
            '5' => 'Mai',
            '6' => 'Juin',
            '7' => 'Juillet',
            '8' => 'Août',
            '9' => 'Septembre',
            '10' => 'Octobre',
            '11' => 'Novembre',
            '12' => 'Décembre'
        ];

        $years = range(now()->year, now()->year - 4);

        return view('livewire.activities.index', [
            'timesheets' => $activities,
            'months' => $months,
            'years' => $years
        ]);
    }

    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail("activites.supprimer");

        $activity = Activity::where('user_id', $this->user_id)->findOrFail($id);

        // Sécurité métier : On empêche la suppression d'une activité déjà validée ou soumise
        if ($activity->status !== 'brouillon' && $activity->status !== 'rejeté') {
            throw ValidationException::withMessages([
                'activity' => ["Action impossible : Une activité transmise ou validée ne peut plus être supprimée."]
            ]);
        }

        $this->deleteId = $activity->id;
        $this->deleteName = $activity->titre . ' (' . $activity->activity_date->format('d/m/Y') . ')';

        $this->dispatch('open-modal', id: 'delete-activity-modal');
    }

    public function delete(int $id)
    {
        $this->checkPermissionOrFail("activites.supprimer");

        if ($this->deleteId === $id) {
            $activity = Activity::where('id', $id)->where('user_id', $this->user_id);
            $activity->delete();
            session()->flash('success', 'L\'activité a été retirée de votre feuille de temps avec succès.');
        }

        $this->deleteId = null;
        $this->deleteName = null;
        $this->dispatch('close-modal', 'delete-activity-modal');
    }
}
