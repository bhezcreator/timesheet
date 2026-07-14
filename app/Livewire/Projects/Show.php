<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public int $projectId;

    // Variables pour l'état du tableau de bord
    public array $stats = [];

    // APRÈS (Correction validée)
    public function mount(int $projectId)
    {
        $this->projectId = $projectId;
        $this->checkPermissionOrFail('projets.voir');
        $this->calculateProjectDashboardStats();
    }

    /**
     * Calcule toutes les statistiques du mini-dashboard de manière optimisée.
     */
    public function calculateProjectDashboardStats()
    {
        $project = Project::query()
            ->with([
                'manager',
                'users',
                'subProjects' => function ($query) {
                    $query->withCount('activities');
                },
                'activities'
            ])
            ->withCount(['subProjects', 'activities', 'users'])
            ->findOrFail($this->projectId);

        // 1. Calcul de l'avancement global
        // On considère l'avancement combiné des sous-projets et activités
        $totalSubProjects = $project->sub_projects_count;
        $completedSubProjects = $project->subProjects->where('status', 'Terminé')->count();

        $totalActivities = $project->activities_count;
        $completedActivities = $project->activities->where('status', 'Terminé')->count();

        // Calcul du pourcentage pondéré
        $subProjectProgress = $totalSubProjects > 0 ? ($completedSubProjects / $totalSubProjects) * 100 : 0;
        $activityProgress = $totalActivities > 0 ? ($completedActivities / $totalActivities) * 100 : 0;

        $globalProgress = 0;
        if ($totalSubProjects > 0 && $totalActivities > 0) {
            $globalProgress = round(($subProjectProgress + $activityProgress) / 2);
        } elseif ($totalSubProjects > 0) {
            $globalProgress = round($subProjectProgress);
        } elseif ($totalActivities > 0) {
            $globalProgress = round($activityProgress);
        }

        // 2. Formatage des données pour la vue
        $this->stats = [
            'project' => $project,
            'global_progress' => $globalProgress,
            'sub_projects_completed' => $completedSubProjects,
            'activities_completed' => $completedActivities,
            'days_remaining' => $project->end_date ? now()->diffInDays($project->end_date, false) : null,
        ];
    }

    /**
     * Sécurité standard de votre application.
     */
    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ["Action non autorisée : Privilèges insuffisants pour consulter ce projet."]
        ]);
    }

    public function render()
    {
        return view('livewire.projects.show', $this->stats);
    }
}
