<?php

namespace App\Livewire\Rapports;

use App\Models\MonthlyReport;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class RapportIndex extends Component
{
    use WithPagination;

    // Propriétés des filtres bindées via @entangle
    public $month = '';

    public $year = '';

    public $selected_project_id = 'all';

    // Gestion Modal
    public bool $showDeleteModal = false;

    // Propriétés pour la modale de suppression
    public $deleteId = null;

    public $deleteName = '';

    public int $user_id;

    // Réinitialiser la pagination lors du changement de filtres
    public function updatedMonth()
    {
        $this->resetPage();
    }

    public function updatedYear()
    {
        $this->resetPage();
    }

    public function updatedSelectedProjectId()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->user_id = Auth::id();
        $this->year = date('Y');
    }

    public function confirmDelete($reportId)
    {
        $this->checkPermissionOrFail('rapports.supprimer');
        $report = MonthlyReport::where('user_id', $this->user_id)->findOrFail($reportId);
        $this->deleteId = $report->id;
        // Nom affiché dans la modale : exemple "Rapport de Janvier 2026"
        $this->deleteName = $report->full_title;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->checkPermissionOrFail('rapports.supprimer');
        if ($this->deleteId) {
            $report = MonthlyReport::where('user_id', $this->user_id)->find($this->deleteId);

            if ($report) {
                // Optionnel : Dissocier les activités liées avant la suppression
                $report->activities()->update(['monthly_report_id' => null]);
                $report->delete();

                session()->flash('message', 'Le rapport a été supprimé avec succès.');
            }
        }

        $this->closeDeleteModal();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->reset(['deleteId', 'deleteName']);
    }

    public function render()
    {
        // 1. Projets affectés à l'utilisateur connecté
        $user = User::find($this->user_id);
        $projects = $user->projects()->get();
        $assignedProjectIds = $projects->pluck('id')->toArray();

        // 2. Requête de filtrage des rapports
        $query = MonthlyReport::with(['activities', 'validation'])
            ->where('user_id', $this->user_id);

        if ($this->month !== '') {
            $query->where('month', $this->month);
        }

        if ($this->year !== '') {
            $query->where('year', $this->year);
        }

        // Si filtrage par projet spécifique
        if ($this->selected_project_id !== 'all') {
            $query->whereHas('activities', function ($q) {
                $q->where('project_id', $this->selected_project_id);
            });
        } else {
            // Filtrage global de sécurité : n'afficher que les rapports contenant des activités
            // des projets assignés (ou les rapports vides créés par l'user)
            $query->where(function ($q) use ($assignedProjectIds) {
                $q->whereHas('activities', function ($subQ) use ($assignedProjectIds) {
                    $subQ->whereIn('project_id', $assignedProjectIds);
                })->orWhereDoesntHave('activities');
            });
        }

        $reports = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(3);

        return view('livewire.rapports.rapport-index', [
            'reports' => $reports,
            'projects' => $projects,
        ]);
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
            'permission' => ['Action non autorisée : Privilèges insuffisants pour exécuter cette opération.'],
        ]);
    }
}
