<?php

namespace App\Livewire\Presente;

use App\Models\AttendanceRecord;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Filtres
    public string $filterMode = 'month'; // month, week, day
    public string $selectedDate = '';
    public ?int $officeId = null;
    public string $search = '';

    // Statistiques
    public array $stats = [
        'total' => 0,
        'present' => 0,
        'late' => 0,
        'absent' => 0,
    ];

    // Calendrier
    public array $calendarData = [];
    public array $monthDays = [];
    public string $currentMonth = '';
    public int $currentYear = 0;

    protected $queryString = [
        'filterMode' => ['except' => 'month'],
        'selectedDate' => ['except' => ''],
        'officeId' => ['except' => null],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->currentMonth = Carbon::now()->format('F');
        $this->currentYear = Carbon::now()->year;
        $this->loadCalendarData();
    }

    public function render()
    {
        $user = Auth::user();
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        // Récupérer les présences
        $attendances = $this->getAttendances($user);

        // Calculer les statistiques
        $this->calculateStats($attendances);

        // Données du calendrier
        $this->loadCalendarData();

        return view('livewire.presente.index', [
            'attendances' => $attendances,
            'offices' => $offices,
            'user' => $user,
        ]);
    }

    protected function getAttendances($user)
    {
        $query = AttendanceRecord::with(['office', 'user'])
            ->where('user_id', $user->id);

        // Filtre par date
        if ($this->filterMode === 'day') {
            $query->whereDate('attendance_date', $this->selectedDate);
        } elseif ($this->filterMode === 'week') {
            $startOfWeek = Carbon::parse($this->selectedDate)->startOfWeek();
            $endOfWeek = Carbon::parse($this->selectedDate)->endOfWeek();
            $query->whereBetween('attendance_date', [$startOfWeek, $endOfWeek]);
        } elseif ($this->filterMode === 'month') {
            $date = Carbon::parse($this->selectedDate);
            $query->whereYear('attendance_date', $date->year)
                ->whereMonth('attendance_date', $date->month);
        }

        // Filtre par bureau
        if ($this->officeId) {
            $query->where('office_id', $this->officeId);
        }

        // Recherche
        if ($this->search) {
            $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('type', 'like', $searchTerm)
                    ->orWhere('status', 'like', $searchTerm)
                    ->orWhere('reason', 'like', $searchTerm)
                    ->orWhereHas('office', function ($q2) use ($searchTerm) {
                        $q2->where('name', 'like', $searchTerm);
                    });
            });
        }

        return $query->orderBy('attendance_date', 'desc')
            ->orderBy('recorded_at', 'desc')
            ->paginate(15);
    }

    protected function calculateStats($attendances)
    {
        $collection = $attendances->getCollection();

        $this->stats = [
            'total' => $collection->count(),
            'present' => $collection->where('status', 'present')->count(),
            'late' => $collection->where('status', 'late')->count(),
            'absent' => $collection->where('status', 'absent')->count(),
        ];
    }

    protected function loadCalendarData()
    {
        $user = Auth::user();
        $date = Carbon::parse($this->selectedDate);
        $year = $date->year;
        $month = $date->month;

        // Jours du mois
        $daysInMonth = $date->daysInMonth;
        $firstDayOfMonth = Carbon::create($year, $month, 1)->dayOfWeek;

        $this->monthDays = [];
        for ($i = 0; $i < $daysInMonth; $i++) {
            $currentDate = Carbon::create($year, $month, $i + 1);
            $this->monthDays[] = [
                'day' => $i + 1,
                'date' => $currentDate->format('Y-m-d'),
                'isToday' => $currentDate->isToday(),
                'isWeekend' => $currentDate->isWeekend(),
                'hasAttendance' => $this->hasAttendanceOnDate($user->id, $currentDate->format('Y-m-d')),
                'attendanceCount' => $this->getAttendanceCountOnDate($user->id, $currentDate->format('Y-m-d')),
            ];
        }

        $this->currentMonth = $date->format('F Y');
        $this->currentYear = $year;
    }

    protected function hasAttendanceOnDate($userId, $date)
    {
        return AttendanceRecord::where('user_id', $userId)
            ->whereDate('attendance_date', $date)
            ->exists();
    }

    protected function getAttendanceCountOnDate($userId, $date)
    {
        return AttendanceRecord::where('user_id', $userId)
            ->whereDate('attendance_date', $date)
            ->count();
    }

    // Navigation dans le calendrier
    public function previousPeriod()
    {
        $date = Carbon::parse($this->selectedDate);
        if ($this->filterMode === 'day') {
            $date->subDay();
        } elseif ($this->filterMode === 'week') {
            $date->subWeek();
        } else {
            $date->subMonth();
        }
        $this->selectedDate = $date->format('Y-m-d');
        $this->loadCalendarData();
    }

    public function nextPeriod()
    {
        $date = Carbon::parse($this->selectedDate);
        if ($this->filterMode === 'day') {
            $date->addDay();
        } elseif ($this->filterMode === 'week') {
            $date->addWeek();
        } else {
            $date->addMonth();
        }
        $this->selectedDate = $date->format('Y-m-d');
        $this->loadCalendarData();
    }

    public function goToToday()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->loadCalendarData();
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->filterMode = 'day';
        $this->loadCalendarData();
    }

    // Filtres
    public function setFilterMode($mode)
    {
        $this->filterMode = $mode;
        $this->resetPage();
    }

    public function updatedOfficeId()
    {
        $this->resetPage();
        $this->loadCalendarData();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedDate()
    {
        $this->loadCalendarData();
    }

    // Export
    public function exportCsv()
    {
        $user = Auth::user();
        $attendances = $this->getAttendances($user)->getCollection();

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

    // Obtenir la couleur du statut
    public function getStatusColor($status)
    {
        return match ($status) {
            'present' => 'emerald',
            'late' => 'amber',
            'absent' => 'red',
            default => 'gray',
        };
    }

    // Obtenir l'icône du type
    public function getTypeIcon($type)
    {
        return match ($type) {
            'check_in' => 'la-sign-in-alt',
            'check_out' => 'la-sign-out-alt',
            default => 'la-clock',
        };
    }

    // Obtenir le libellé du type
    public function getTypeLabel($type)
    {
        return match ($type) {
            'check_in' => 'Arrivée',
            'check_out' => 'Départ',
            default => ucfirst($type),
        };
    }
}