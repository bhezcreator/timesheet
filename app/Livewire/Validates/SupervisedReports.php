<?php

namespace App\Livewire\Validates;

use App\Models\MonthlyReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\User;

#[Layout('layouts.app')]
class SupervisedReports extends Component
{
    use WithPagination;

    public $month = '';
    public $year = '';
    public $selected_user_id = '';
    public $selected_project_id = 'all';

    public function updatedMonth()
    {
        $this->resetPage();
    }

    public function updatedYear()
    {
        $this->resetPage();
    }

    public function updatedSelectedUserId()
    {
        $this->resetPage();
    }

    public function updatedSelectedProjectId()
    {
        $this->resetPage();
    }

    public function render()
    {
        $supervisor = Auth::user();

        $subordinates = User::where('supervisor_id', $supervisor->id)
            ->orderBy('first_name')
            ->get();

        $projects = Project::orderBy('name')->get();

        $reports = MonthlyReport::query()
            ->with([
                'user',
                'activities',
                'media',
                'validation'
            ])
            ->whereHas('user', function ($query) use ($supervisor) {
                $query->where('supervisor_id', $supervisor->id);
            });

        /*
    |--------------------------------------------------------------------------
    | Filtres
    |--------------------------------------------------------------------------
    */

        if ($this->selected_user_id != '') {
            $reports->where('user_id', $this->selected_user_id);
        }

        if ($this->month != '') {
            $reports->where('month', $this->month);
        }

        if ($this->year != '') {
            $reports->where('year', $this->year);
        }

        if ($this->selected_project_id != 'all') {
            $reports->where(function ($query) {
                $query->whereJsonContains('project_ids', (int)$this->selected_project_id)
                    ->orWhereHas('activities', function ($activity) {
                        $activity->where('project_id', $this->selected_project_id);
                    });
            });
        }

        return view('livewire.validates.supervised-reports', [
            'reports' => $reports
                ->latest('submitted_at')
                ->paginate(10),
            'subordinates' => $subordinates,
            'projects' => $projects,
        ]);
    }
}
