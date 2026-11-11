<?php

namespace App\Livewire\WorkSchedule;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WorkSchedule;
use App\Models\Office;

class Index extends Component
{
    use WithPagination;

    // Variables de formulaire
    public string $name = '';
    public int $office_id;
    public int $day_of_week = 1;
    public string $start_time = '08:00';
    public string $end_time = '17:00';
    public int $grace_period = 15;
    public bool $is_active = true;

    public ?int $scheduleId = null;

    // Gestion des Modales
    public bool $showModal = false;
    public bool $showDeleteModal = false;

    // Variables de suppression
    public ?int $deleteId = null;
    public ?string $deleteName = null;

    // Filtre de recherche
    public string $search = '';
    public ?int $filterOffice = null;
    public ?int $filterDay = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterOffice' => ['except' => ''],
        'filterDay' => ['except' => ''],
    ];

    protected function rules()
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'regex:/^[a-zA-Z0-9\s\- éèà_]+$/',
                $this->scheduleId 
                    ? 'unique:work_schedules,name,' . $this->scheduleId 
                    : 'unique:work_schedules,name'
            ],
            'office_id' => ['required', 'exists:offices,id'],
            'day_of_week' => [
                'required', 
                'integer', 
                'between:0,6',
                $this->validateUniqueSchedule()
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'grace_period' => ['required', 'integer', 'min:0', 'max:60'],
            'is_active' => ['boolean'],
        ];
    }

    protected function validateUniqueSchedule()
    {
        return function ($attribute, $value, $fail) {
            $exists = \App\Models\WorkSchedule::where('office_id', $this->office_id)
                ->where('day_of_week', $value)
                ->when($this->scheduleId, function ($query) {
                    $query->where('id', '!=', $this->scheduleId);
                })
                ->exists();
            
            if ($exists) {
                $office = \App\Models\Office::find($this->office_id);
                $dayName = $this->getDayName($value);
                $fail("Un horaire existe déjà pour '{$office->name}' le {$dayName}.");
            }
        };
    }

    protected function messages()
    {
        return [
            'name.required' => 'Le nom de l\'horaire est obligatoire.',
            'name.regex' => 'Le nom ne peut contenir que des lettres, chiffres, espaces, tirets et underscores.',
            'name.unique' => 'Cet nom d\'horaire existe déjà.',
            'office_id.required' => 'Veuillez sélectionner un bureau.',
            'office_id.exists' => 'Le bureau sélectionné n\'existe pas.',
            'day_of_week.required' => 'Veuillez sélectionner un jour de la semaine.',
            'day_of_week.between' => 'Le jour doit être entre 0 (Dimanche) et 6 (Samedi).',
            'start_time.required' => 'L\'heure de début est obligatoire.',
            'start_time.date_format' => 'Format d\'heure invalide (HH:MM).',
            'end_time.required' => 'L\'heure de fin est obligatoire.',
            'end_time.date_format' => 'Format d\'heure invalide (HH:MM).',
            'end_time.after' => 'L\'heure de fin doit être après l\'heure de début.',
            'grace_period.required' => 'La période de grâce est obligatoire.',
            'grace_period.integer' => 'La période de grâce doit être un nombre entier.',
            'grace_period.min' => 'La période de grâce doit être d\'au moins 0 minute.',
            'grace_period.max' => 'La période de grâce ne peut pas dépasser 60 minutes.',
            'is_active.boolean' => 'Le statut doit être vrai ou faux.',
        ];
    }

    protected function getDayName($day)
    {
        return [
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
        ][$day] ?? 'Jour inconnu';
    }

    // Gestion des permissions
    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ['Action non autorisée : Privilèges insuffisants pour exécuter cette opération.'],
        ]);
    }

    // Rendu principal
    public function render()
    {
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $this->search) . '%';

        $schedules = WorkSchedule::query()
            ->with('office')
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm);
            })
            ->when($this->filterOffice, function ($query) {
                $query->where('office_id', $this->filterOffice);
            })
            ->when($this->filterDay !== null && $this->filterDay !== '', function ($query) {
                $query->where('day_of_week', $this->filterDay);
            })
            ->latest()
            ->paginate(10);

        $offices = Office::query()
            ->orderBy('name')
            ->get();

        $daysOfWeek = [
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
        ];

        return view('livewire.work-schedule.index', [
            'schedules' => $schedules,
            'offices' => $offices,
            'daysOfWeek' => $daysOfWeek,
        ]);
    }

    // Ouvrir la modale de création
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    // Ouvrir la modale d'édition
    public function edit($id)
    {
        $this->checkPermissionOrFail('horraire.modifier');

        $schedule = WorkSchedule::findOrFail($id);
        $this->scheduleId = $schedule->id;
        $this->name = $schedule->name;
        $this->office_id = $schedule->office_id;
        $this->day_of_week = $schedule->day_of_week;
        $this->start_time = $schedule->start_time;
        $this->end_time = $schedule->end_time;
        $this->grace_period = $schedule->grace_period;
        $this->is_active = $schedule->is_active;
        $this->showModal = true;
        $this->showDeleteModal = false;
    }

    // Sauvegarder (création ou modification)
    public function save()
    {
        if ($this->scheduleId) {
            $this->checkPermissionOrFail('horraire.modifier');
            $this->validate();
        } else {
            $this->checkPermissionOrFail('horraire.creer');
            $this->validate();
        }

        // Vérifier les conflits d'horaires
        $this->validateScheduleConflict();

        $scheduleData = [
            'name' => trim($this->name),
            'office_id' => $this->office_id,
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'grace_period' => $this->grace_period,
            'is_active' => $this->is_active,
        ];

        if ($this->scheduleId) {
            $schedule = WorkSchedule::findOrFail($this->scheduleId);
            $schedule->update($scheduleData);
            $message = 'Horaire modifié avec succès.';
        } else {
            WorkSchedule::create($scheduleData);
            $message = 'Horaire créé avec succès.';
        }

        session()->flash('success', $message);
        $this->closeModal();
    }

    // Vérifier les conflits d'horaires
    private function validateScheduleConflict()
    {
        $conflict = WorkSchedule::query()
            ->where('office_id', $this->office_id)
            ->where('day_of_week', $this->day_of_week)
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                    ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                    ->orWhere(function ($q) {
                        $q->where('start_time', '<=', $this->start_time)
                            ->where('end_time', '>=', $this->end_time);
                    });
            });

        if ($this->scheduleId) {
            $conflict->where('id', '!=', $this->scheduleId);
        }

        if ($conflict->exists()) {
            throw ValidationException::withMessages([
                'day_of_week' => ['Un horaire existe déjà pour ce jour dans ce bureau avec des heures qui se chevauchent.'],
            ]);
        }
    }

    // Confirmer la suppression
    public function confirmDelete(int $id)
    {
        $this->checkPermissionOrFail('horraire.supprimer');

        $schedule = WorkSchedule::findOrFail($id);
        $this->deleteId = $schedule->id;
        $this->deleteName = $schedule->name;
        $this->showDeleteModal = true;
        $this->showModal = false;
    }

    // Exécuter la suppression
    public function delete()
    {
        $this->checkPermissionOrFail('horraire.supprimer');

        if ($this->deleteId) {
            $schedule = WorkSchedule::findOrFail($this->deleteId);
            $schedule->delete();
            session()->flash('success', 'Horaire supprimé avec succès.');
        }

        $this->closeDeleteModal();
    }

    // Fermer toutes les modales
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    // Fermer la modale de suppression
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->showModal = false;
        $this->deleteId = null;
        $this->deleteName = null;
    }

    // Réinitialiser le formulaire
    private function resetForm()
    {
        $this->reset([
            'name',
            'office_id',
            'day_of_week',
            'start_time',
            'end_time',
            'grace_period',
            'is_active',
            'scheduleId'
        ]);
        $this->resetValidation();
    }

    // Gestion de la recherche
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterOffice()
    {
        $this->resetPage();
    }

    public function updatingFilterDay()
    {
        $this->resetPage();
    }

    // Réinitialiser les filtres
    public function resetFilters()
    {
        $this->reset(['search', 'filterOffice', 'filterDay']);
        $this->resetPage();
    }

    // Basculer le statut actif/inactif
    public function toggleActive($id)
    {
        $this->checkPermissionOrFail('horraire.modifier');
        
        $schedule = WorkSchedule::findOrFail($id);
        $schedule->update(['is_active' => !$schedule->is_active]);
        
        session()->flash('success', 'Statut de l\'horaire mis à jour.');
    }
}