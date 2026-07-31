<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class ActivityLogs extends Component
{
    use WithPagination;

    public $search = '';
    public $logName = '';
    public $event = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedLog = null;
    public $showDetailsModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'logName' => ['except' => ''],
        'event' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'perPage' => ['except' => 15],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $rules = [
        'search' => 'nullable|string|max:255',
        'logName' => 'nullable|string|max:100',
        'event' => 'nullable|string|max:50',
        'dateFrom' => 'nullable|date',
        'dateTo' => 'nullable|date|after_or_equal:dateFrom',
        'perPage' => 'required|in:10,15,25,50,100',
    ];

    public function getListeners()
    {
        return [
            'refreshLogs' => '$refresh',
        ];
    }

    public function mount()
    {
        $this->perPage = session('activity_logs_per_page', 15);
    }

    public function updatedPerPage($value)
    {
        session(['activity_logs_per_page' => $value]);
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedLogName()
    {
        $this->resetPage();
    }

    public function updatedEvent()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function viewDetails($logId)
    {
        $this->selectedLog = Activity::with('causer')->find($logId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedLog = null;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->logName = '';
        $this->event = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function getLogNamesProperty()
    {
        return Activity::select('log_name')
            ->distinct()
            ->whereNotNull('log_name')
            ->pluck('log_name')
            ->toArray();
    }

    public function getEventsProperty()
    {
        return Activity::select('event')
            ->distinct()
            ->whereNotNull('event')
            ->pluck('event')
            ->toArray();
    }

    public function getLogsProperty()
    {
        $query = Activity::with(['causer', 'subject'])
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';
                return $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', $search)
                        ->orWhere('log_name', 'like', $search)
                        ->orWhere('event', 'like', $search)
                        ->orWhere('subject_type', 'like', $search)
                        ->orWhere('causer_type', 'like', $search)
                        ->orWhereHas('causer', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'like', $search)
                                ->orWhere('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        });
                });
            })
            ->when($this->logName, function ($query) {
                return $query->where('log_name', $this->logName);
            })
            ->when($this->event, function ($query) {
                return $query->where('event', $this->event);
            })
            ->when($this->dateFrom, function ($query) {
                return $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                return $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function getEventsCountProperty()
    {
        return [
            'total' => Activity::count(),
            'created' => Activity::where('event', 'created')->count(),
            'updated' => Activity::where('event', 'updated')->count(),
            'deleted' => Activity::where('event', 'deleted')->count(),
        ];
    }

    public function getEventBadgeColor($event)
    {
        return match ($event) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
            default => 'secondary',
        };
    }

    public function getEventIcon($event)
    {
        return match ($event) {
            'created' => 'la-plus-circle',
            'updated' => 'la-edit',
            'deleted' => 'la-trash',
            'restored' => 'la-undo',
            default => 'la-circle',
        };
    }

    public function getSubjectTypeName($type)
    {
        if (empty($type)) {
            return 'N/A';
        }
        $parts = explode('\\', $type);
        return end($parts);
    }

    public function getModelColor($model)
    {
        $colors = [
            'User' => 'primary',
            'Project' => 'info',
            'SubProject' => 'success',
            'Activity' => 'warning',
            'MonthlyReport' => 'danger',
            'ReportValidation' => 'secondary',
            'ActivityType' => 'purple',
            'BlockedDay' => 'orange',
            'Setting' => 'teal',
        ];

        return $colors[$model] ?? 'secondary';
    }

    public function getModelIcon($model)
    {
        $icons = [
            'User' => 'la-user',
            'Project' => 'la-project-diagram',
            'SubProject' => 'la-code-branch',
            'Activity' => 'la-tasks',
            'MonthlyReport' => 'la-file-alt',
            'ReportValidation' => 'la-check-circle',
            'ActivityType' => 'la-tag',
            'BlockedDay' => 'la-calendar-times',
            'Setting' => 'la-cog',
        ];

        return $icons[$model] ?? 'la-file';
    }

    public function render()
    {
        return view('livewire.activity-logs', [
            'logs' => $this->logs,
            'logNames' => $this->log_names,
            'events' => $this->events,
            'eventsCount' => $this->events_count,
        ]);
    }
}
