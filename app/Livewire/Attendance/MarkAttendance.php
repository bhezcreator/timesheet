<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceRecord;
use App\Models\BlockedDay;
use App\Models\Office;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MarkAttendance extends Component
{
    public int $userId;
    public ?User $user = null;

    // Champs du formulaire
    public ?int $officeId = null;
    public string $type = 'arrivée';
    public string $attendanceDate;      // date simple OU date de début
    public ?string $endDate = null;     // date de fin (mode plage)
    public bool $isRangeMode = false;   // toggle
    public string $recordedAt;
    public string $status = 'présent';
    public string $reason = '';

    // Localisation (facultative en mode superviseur)
    public ?float $latitude  = null;
    public ?float $longitude = null;
    public ?float $accuracy  = null;
    public ?float $distance  = null;

    public function mount(int $userId): void
    {
        $this->userId = $userId;
        $this->user   = User::findOrFail($userId);

        $this->attendanceDate = now()->format('Y-m-d');
        $this->recordedAt     = now()->format('H:i');

        if ($this->user->office_id) {
            $this->officeId = $this->user->office_id;
        } else {
        $office = Office::where('is_active', true)->first();

        $this->officeId = $office?->id;
        }
    }

    public function updatedIsRangeMode($value): void
    {
        if (! $value) {
            $this->endDate = null;
        }
        $this->resetValidation();
    }

    protected function rules(): array
    {
        $rules = [
            'type'       => ['required', 'in:arrivée,départ,pause,reprise'],
            'recordedAt' => ['required', 'date_format:H:i'],
            'status'     => ['required', 'in:présent,retard,absent,congé,mission'],
            'reason'     => ['nullable', 'string', 'max:500'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy'   => ['nullable', 'numeric', 'min:0'],
            'distance'   => ['nullable', 'numeric', 'min:0'],
        ];

        if ($this->isRangeMode) {
            $rules['attendanceDate'] = ['required', 'date'];
            $rules['endDate'] = ['required', 'date', 'after_or_equal:attendanceDate', 'before_or_equal:today'];
        } else {
            $rules['attendanceDate'] = ['required', 'date', 'before_or_equal:today'];
        }

        return $rules;
    }

    protected $messages = [
        'type.required'                  => 'Veuillez sélectionner un type.',
        'type.in'                        => 'Type de présence invalide.',
        'attendanceDate.required'        => 'La date est obligatoire.',
        'attendanceDate.before_or_equal' => 'La date ne peut pas être dans le futur.',
        'endDate.required'               => 'La date de fin est obligatoire.',
        'endDate.after_or_equal'         => 'La date de fin doit être ≥ à la date de début.',
        'endDate.before_or_equal'        => 'La date de fin ne peut pas être dans le futur.',
        'recordedAt.required'            => 'L\'heure est obligatoire.',
        'status.required'                => 'Le statut est obligatoire.',
    ];

    /**
     * Liste des dates réellement prises en compte selon le mode.
     * En mode plage : exclut dimanches + jours bloqués actifs.
     */
    public function getEffectiveDatesProperty(): array
    {
        if (! $this->attendanceDate) return [];

        if (! $this->isRangeMode) {
            return [$this->attendanceDate];
        }

        if (! $this->endDate) return [];

        try {
            $start = Carbon::parse($this->attendanceDate)->startOfDay();
            $end   = Carbon::parse($this->endDate)->startOfDay();
        } catch (\Throwable $e) {
            return [];
        }

        if ($end->lt($start)) return [];

        $blocked = BlockedDay::query()
            ->where('is_active', true)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->map(fn ($d) => Carbon::parse($d->date)->toDateString())
            ->all();

        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->isSunday()) continue;
            if (in_array($d->toDateString(), $blocked, true)) continue;
            $dates[] = $d->toDateString();
        }

        return $dates;
    }

    /**
     * Aperçu : pour chaque date, indique si un enregistrement existe déjà.
     */
    public function getPreviewDatesProperty(): array
    {
        $dates = $this->effectiveDates;
        if (empty($dates)) return [];

        $existing = AttendanceRecord::where('user_id', $this->userId)
            ->where('type', $this->type)
            ->whereIn('attendance_date', $dates)
            ->pluck('attendance_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->all();

        return array_map(function ($date) use ($existing) {
            $carbon = Carbon::parse($date)->locale('fr');
            return [
                'date'   => $date,
                'exists' => in_array($date, $existing, true),
                'label'  => $carbon->isoFormat('ddd D MMM'),
            ];
        }, $dates);
    }

    /**
     * En mode simple uniquement : avertit si la date choisie est non ouvrable.
     */
    public function getSingleDateWarningProperty(): ?string
    {
        if ($this->isRangeMode || ! $this->attendanceDate) return null;

        try {
            $date = Carbon::parse($this->attendanceDate);
        } catch (\Throwable $e) {
            return null;
        }

        if ($date->isSunday()) {
            return 'Dimanche sélectionné — l\'enregistrement manuel reste possible.';
        }

        $blocked = BlockedDay::where('is_active', true)
            ->whereDate('date', $date)
            ->first();

        if ($blocked) {
            return "Jour non ouvrable ({$blocked->name}) — l'enregistrement manuel reste possible.";
        }

        return null;
    }

    public function save()
    {
        $this->validate();

        $dates = $this->effectiveDates;

        if (empty($dates)) {
            throw ValidationException::withMessages([
                'attendanceDate' => ['Aucun jour ouvrable dans la plage sélectionnée (les dimanches et jours non ouvrables sont exclus).'],
            ]);
        }

        $created = 0;
        $skipped = 0;

        foreach ($dates as $date) {
            $exists = AttendanceRecord::where('user_id', $this->userId)
                ->where('attendance_date', $date)
                ->where('type', $this->type)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            AttendanceRecord::create([
                'user_id'         => $this->userId,
                'office_id'       => $this->officeId,
                'type'            => $this->type,
                'attendance_date' => $date,
                'recorded_at'     => Carbon::parse($date . ' ' . $this->recordedAt),
                'latitude'        => $this->latitude,
                'longitude'       => $this->longitude,
                'accuracy'        => $this->accuracy,
                'distance'        => $this->distance,
                'qr_token'        => null,
                'ip_address'      => request()->ip(),
                'user_agent'      => substr((string) request()->userAgent(), 0, 500),
                'status'          => $this->status,
                'reason'          => $this->reason ?: null,
            ]);

            $created++;
        }

        if ($created === 0) {
            $msg = $this->isRangeMode
                ? 'Toutes les dates sélectionnées ont déjà un enregistrement de ce type.'
                : "Une présence de type « {$this->type} » existe déjà pour cette date.";

            throw ValidationException::withMessages(['duplicate' => [$msg]]);
        }

        $message = $created . ' présence(s) enregistrée(s)';
        if ($skipped > 0) {
            $message .= ' — ' . $skipped . ' date(s) ignorée(s) (déjà existante(s))';
        }
        $message .= ' pour ' . $this->user->name . '.';

        session()->flash('success', $message);

        return redirect()->route('activities.supervise', ['userId' => $this->userId]);
    }

    public function resetForm(): void
    {
        $this->reset(['officeId', 'reason', 'latitude', 'longitude', 'accuracy', 'distance', 'endDate']);
        $this->isRangeMode    = false;
        $this->type           = 'arrivée';
        $this->status         = 'présent';
        $this->attendanceDate = now()->format('Y-m-d');
        $this->recordedAt     = now()->format('H:i');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.attendance.mark-attendance', [
            'offices'           => Office::where('is_active', true)->orderBy('name')->get(),
            'previewDates'      => $this->previewDates,
            'singleDateWarning' => $this->singleDateWarning,
        ]);
    }
}