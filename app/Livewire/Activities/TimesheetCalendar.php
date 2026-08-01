<?php

namespace App\Livewire\Activities;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TimesheetCalendar extends Component
{
    // Propriétés de navigation
    public $viewMode = 'month'; // 'year', 'month', 'week'

    public $currentDate;

    // Propriétés pour les modales
    public $selectedActivity = null;

    public $deleteId = null;

    public $deleteName = '';

    public function mount()
    {
        $this->currentDate = now()->startOfDay()->format('Y-m-d');
    }

    public function changeView($mode)
    {
        $this->viewMode = $mode;
    }

    public function next()
    {
        $date = Carbon::parse($this->currentDate);
        if ($this->viewMode === 'year') {
            $date->addYear();
        } elseif ($this->viewMode === 'month') {
            $date->addMonth();
        } elseif ($this->viewMode === 'week') {
            $date->addWeek();
        }
        $this->currentDate = $date->format('Y-m-d');
    }

    public function previous()
    {
        $date = Carbon::parse($this->currentDate);
        if ($this->viewMode === 'year') {
            $date->subYear();
        } elseif ($this->viewMode === 'month') {
            $date->subMonth();
        } elseif ($this->viewMode === 'week') {
            $date->subWeek();
        }
        $this->currentDate = $date->format('Y-m-d');
    }

    public function today()
    {
        $this->currentDate = now()->startOfDay()->format('Y-m-d');
    }

    // Gestion de la modale de détails
    public function showDetails($activityId)
    {
        $this->selectedActivity = Activity::with(['project', 'subProject', 'activityType'])
            ->where('user_id', Auth::id())
            ->find($activityId);

        if ($this->selectedActivity) {
            $this->dispatch('open-modal', id: 'details-activity-modal');
        }
    }

    // Préparation de la suppression
    public function confirmDelete($activityId, $title)
    {
        $this->deleteId = $activityId;
        $this->deleteName = $title;
        $this->dispatch('open-modal', id: 'delete-activity-modal');
    }

    // Action de suppression effective
    public function delete()
    {
        if ($this->deleteId) {
            Activity::where('user_id', Auth::id())->where('id', $this->deleteId)->delete();
            $this->dispatch('close-modal', id: 'delete-activity-modal');
            $this->reset(['deleteId', 'deleteName']);
            session()->flash('message', 'Activité supprimée avec succès.');
        }
    }

    public function render()
    {
        $date = Carbon::parse($this->currentDate);
        $activities = $this->getActivities($date);

        return view('livewire.activities.timesheet-calendar', [
            'activities' => $activities,
            'calendarData' => $this->buildCalendarData($date),
            'navigationTitle' => $this->getNavigationTitle($date),
        ]);
    }

    private function getActivities(Carbon $date)
    {
        $query = Activity::with(['project', 'activityType'])
            ->where('user_id', Auth::id());

        if ($this->viewMode === 'year') {
            $query->whereYear('activity_date', $date->year);
        } elseif ($this->viewMode === 'month') {
            $query->whereYear('activity_date', $date->year)
                ->whereMonth('activity_date', $date->month);
        } else { // week
            $query->whereBetween('activity_date', [
                $date->copy()->startOfWeek(),
                $date->copy()->endOfWeek(),
            ]);
        }

        return $query->orderBy('activity_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($item) => $item->activity_date->format('Y-m-d'));
    }

    private function getNavigationTitle(Carbon $date)
    {
        if ($this->viewMode === 'year') {
            return $date->translatedFormat('Y');
        }
        if ($this->viewMode === 'month') {
            return $date->translatedFormat('F Y');
        }

        return 'Semaine '.$date->weekOfYear.' - '.$date->translatedFormat('Y');
    }

    private function buildCalendarData(Carbon $date)
    {
        if ($this->viewMode === 'month') {
            $start = $date->copy()->startOfMonth()->startOfWeek();
            $end = $date->copy()->endOfMonth()->endOfWeek();
        } elseif ($this->viewMode === 'week') {
            $start = $date->copy()->startOfWeek();
            $end = $date->copy()->endOfWeek();
        } else { // Mode Année (Retourne la liste des mois)
            return collect(range(1, 12))->map(fn ($m) => Carbon::create($date->year, $m, 1));
        }

        $days = [];
        while ($start->lte($end)) {
            $days[] = $start->copy();
            $start->addDay();
        }

        return $days;
    }
}
