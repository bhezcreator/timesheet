<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public int $userId;

    public User $user;

    public function mount(int $userId)
    {
        $this->userId = $userId;

        // Eager Loading des relations pour éviter le problème des requêtes N+1
        $this->user = User::with([
            'supervisor',
            'subordinates',
            'projects',
            'subProjects',
            'monthlyReports',
        ])->findOrFail($userId);
    }

    public function render()
    {
        // Stats agrégées par projet et par sous-projet
        $activityStats    = $this->user->activitiesStatsByProject();
        $subActivityStats = $this->user->activitiesStatsBySubProject();

        // Groupement des sous-projets sous leur projet
        $subProjectsByProject = $this->user->subProjects->groupBy('project_id');

        // Sous-projets « orphelins » (l'utilisateur est dessus, mais pas sur le projet parent)
        $projectIds         = $this->user->projects->pluck('id');
        $orphanSubProjects  = $this->user->subProjects->whereNotIn('project_id', $projectIds);

        // Grand total (cohérent avec ce qui est affiché)
        $grandActivities = $this->user->projects->sum(
            fn ($p) => (int) ($activityStats->get($p->id)?->activities_count ?? 0)
        );
        $grandHours = $this->user->projects->sum(
            fn ($p) => (float) ($activityStats->get($p->id)?->total_hours ?? 0)
        );

        foreach ($orphanSubProjects as $sub) {
            $stat = $subActivityStats->get($sub->id);
            $grandActivities += (int) ($stat->activities_count ?? 0);
            $grandHours      += (float) ($stat->total_hours ?? 0);
        }

        return view('livewire.users.show', compact(
            'activityStats',
            'subActivityStats',
            'subProjectsByProject',
            'orphanSubProjects',
            'grandActivities',
            'grandHours',
        ));
    }
}
