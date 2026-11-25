<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Attributes\Layout;
use App\Models\Office;
use App\Models\AttendanceRecord;
use Livewire\Component;
use Carbon\Carbon;

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
        // 1. Stats agrégées (existantes)
        $activityStats    = $this->user->activitiesStatsByProject();
        $subActivityStats = $this->user->activitiesStatsBySubProject();

        // 2. Activités détaillées de l'utilisateur (pour l'affichage des titres)
        $userActivities = $this->user->activities()
            ->with(['activityType:id,name'])
            ->orderByDesc('activity_date')
            ->get();

        // Activités rattachées DIRECTEMENT à un projet (sans sous-projet)
        $activitiesByProject = $userActivities
            ->filter(fn ($a) => $a->project_id && !$a->sub_project_id)
            ->groupBy('project_id');

        // Activités rattachées à un sous-projet
        $activitiesBySubProject = $userActivities
            ->filter(fn ($a) => $a->sub_project_id)
            ->groupBy('sub_project_id');

        // 3. Hiérarchie
        $subProjectsByProject = $this->user->subProjects->groupBy('project_id');
        $projectIds           = $this->user->projects->pluck('id');
        $orphanSubProjects    = $this->user->subProjects->whereNotIn('project_id', $projectIds);

        // 4. Grand total
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

        $offices = Office::where('is_active', true)->orderBy('name')->get();

        // Récupérer les présences
        $attendances = $this->getAttendances($this->user);

        return view('livewire.users.show', compact(
            'activityStats',
            'subActivityStats',
            'activitiesByProject',
            'activitiesBySubProject',
            'subProjectsByProject',
            'orphanSubProjects',
            'grandActivities',
            'grandHours',
            'attendances',
            'offices',
        ));
    }

    protected function getAttendances($user)
    {
        $query = AttendanceRecord::with(['office', 'user'])
            ->where('user_id', $user->id);

        return $query->orderBy('attendance_date', 'desc')
            ->orderBy('recorded_at', 'desc')
            ->paginate(15);
    }

    
    public function getTypeIcon($type): string
    {
        return match ($type) {
            'arrivée', 'check_in' => 'la-sign-in-alt',
            'départ', 'check_out' => 'la-sign-out-alt',
            default => 'la-clock',
        };
    }

    public function getTypeLabel($type): string
    {
        return match ($type) {
            'arrivée' => 'Arrivée au bureau',
            'départ' => 'Départ du bureau',
            'check_in' => 'Arrivée au bureau',
            'check_out' => 'Départ du bureau',
            default => 'Type inconnu',
        };
    }

    public function getStatusColor($status): string
    {
        return match ($status) {
            'présent'  => 'green',
            'absent'   => 'red',
            'retard'   => 'yellow',
            'congé' => 'blue',
            'mission' => 'gray',
            default    => 'gray',
        };
    }

        // Export en Excel
    public function exportCsv()
    {
        $attendances = $this->getAttendances($this->user)->getCollection();

        $filename = 'presences_' . Carbon::now()->format('Y-m-d_H-i') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Heure', 'Bureau', 'Type', 'Statut', 'Motif', 'Localisation']);

            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->attendance_date->format('d/m/Y'),
                    $attendance->recorded_at->format('H:i:s'),
                    $attendance->office?->name ?? 'N/A',
                    $attendance->type,
                    $attendance->status,
                    $attendance->reason ?? '',
                    $attendance->latitude && $attendance->longitude 
                        ? $attendance->latitude . ', ' . $attendance->longitude 
                        : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
