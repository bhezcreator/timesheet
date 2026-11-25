<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceRecord;
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
    public string $attendanceDate;
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

        if (!Gate::allows('presences.creer') && !Gate::allows('presences.enregistrer')) {
            throw ValidationException::withMessages([
                'permission' => ['Action non autorisée : Privilèges insuffisants pour enregistrer une présence.'],
            ]);
        }

        $this->attendanceDate = now()->format('Y-m-d');
        $this->recordedAt     = now()->format('H:i');

        // Pré-sélection si un seul bureau actif
        $offices = Office::where('is_active', true)->get();
        if ($offices->count() === 1) {
            $this->officeId = $offices->first()->id;
        }
    }

    protected function rules(): array
    {
        return [
            'officeId'       => ['required', 'exists:offices,id'],
            'type'           => ['required', 'in:arrivée,départ,pause,reprise'],
            'attendanceDate' => ['required', 'date', 'before_or_equal:today'],
            'recordedAt'     => ['required', 'date_format:H:i'],
            'status'         => ['required', 'in:présent,retard,absent,congé,mission'],
            'reason'         => ['nullable', 'string', 'max:500'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy'       => ['nullable', 'numeric', 'min:0'],
            'distance'       => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected $messages = [
        'officeId.required'             => 'Veuillez sélectionner un bureau.',
        'officeId.exists'               => 'Le bureau sélectionné est invalide.',
        'type.required'                 => 'Veuillez sélectionner un type.',
        'type.in'                       => 'Type de présence invalide.',
        'attendanceDate.required'       => 'La date est obligatoire.',
        'attendanceDate.before_or_equal'=> 'La date ne peut pas être dans le futur.',
        'recordedAt.required'           => 'L\'heure est obligatoire.',
        'status.required'               => 'Le statut est obligatoire.',
    ];

    public function save()
    {
        $this->validate();

        // Prévention des doublons (même agent + même date + même type)
        $exists = AttendanceRecord::where('user_id', $this->userId)
            ->where('attendance_date', $this->attendanceDate)
            ->where('type', $this->type)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'duplicate' => ["Une présence de type « {$this->type} » existe déjà pour cette date."],
            ]);
        }

        $recordedAt = Carbon::parse($this->attendanceDate . ' ' . $this->recordedAt);

        AttendanceRecord::create([
            'user_id'         => $this->userId,
            'office_id'       => $this->officeId,
            'type'            => $this->type,
            'attendance_date' => $this->attendanceDate,
            'recorded_at'     => $recordedAt,
            'latitude'        => $this->latitude,
            'longitude'       => $this->longitude,
            'accuracy'        => $this->accuracy,
            'distance'        => $this->distance,
            'qr_token'        => null, // enregistrement manuel par superviseur
            'ip_address'      => request()->ip(),
            'user_agent'      => substr((string) request()->userAgent(), 0, 500),
            'status'          => $this->status,
            'reason'          => $this->reason ?: null,
        ]);

        session()->flash('success', "Présence enregistrée avec succès pour {$this->user->name}.");

        // ⚠️ Ajuste le nom de route selon ton projet
        return redirect()->route('activities.user', ['userId' => $this->userId]);
    }

    public function resetForm(): void
    {
        $this->reset(['officeId', 'reason', 'latitude', 'longitude', 'accuracy', 'distance']);
        $this->type           = 'arrivée';
        $this->status         = 'présent';
        $this->attendanceDate = now()->format('Y-m-d');
        $this->recordedAt     = now()->format('H:i');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.attendance.mark-attendance', [
            'offices' => Office::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}