<?php

namespace App\Livewire\Users;

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Settings extends Component
{
    // 1. Temps et Calendrier
    public float $time_workday_hours = 8.0;
    public float $time_workweek_hours = 40.0;
    public int $time_first_day_of_week = 1; // 1 = Lundi
    public bool $time_allow_weekend_logging = false;

    // 2. Règles de Saisie et Validations
    public float $timesheet_max_hours_per_day = 12.0;
    public bool $timesheet_allow_future_logging = false;
    public int $timesheet_lock_day_of_month = 5;
    public string $timesheet_period_type = 'monthly';
    public bool $timesheet_require_description = true;

    // 3. Heures Supplémentaires
    public bool $overtime_enabled = false;

    // 4. Préférences d'Affichage
    public string $format_time_input = 'decimal'; // decimal ou duration

    // 5. Heures Supplémentaires (Ajout sous public bool $overtime_enabled = false;)
    public float $overtime_threshold_weekly = 35.0;

    // 6. Rappels et Notifications (Nouvelle catégorie à ajouter)
    public bool $reminder_submit_enabled = true;
    public string $reminder_manager_pending = '08:00';

    protected function rules()
    {
        return [
            'time_workday_hours' => ['required', 'numeric', 'min:1', 'max:24'],
            'time_workweek_hours' => ['required', 'numeric', 'min:1', 'max:168'],
            'time_first_day_of_week' => ['required', 'integer', 'in:0,1'], // 0=Dimanche, 1=Lundi
            'time_allow_weekend_logging' => ['required', 'boolean'],

            'timesheet_max_hours_per_day' => ['required', 'numeric', 'min:1', 'max:24'],
            'timesheet_allow_future_logging' => ['required', 'boolean'],
            'timesheet_lock_day_of_month' => ['required', 'integer', 'min:1', 'max:31'],
            'timesheet_period_type' => ['required', 'string', 'in:weekly,bi-weekly,monthly'],
            'timesheet_require_description' => ['required', 'boolean'],

            'overtime_enabled' => ['required', 'boolean'],
            'overtime_threshold_weekly' => ['required', 'numeric', 'min:0', 'max:168'], // <-- AJOUT

            'format_time_input' => ['required', 'string', 'in:decimal,duration'],

            // --- NOUVELLES RÈGLES DE RAPPELS ---
            'reminder_submit_enabled' => ['required', 'boolean'],
            'reminder_manager_pending' => ['required', 'string', 'regex:/^[0-2][0-9]:[0-5][0-9]$/', 'max:5', 'min:5'],
        ];
    }

    /**
     * Initialisation du composant avec les valeurs de la base de données.
     */
    public function mount()
    {
        $this->checkPermissionOrFail('parametres.voir');

        // Récupère tous les paramètres existants
        $settings = Setting::pluck('value', 'key')->toArray();

        // Hydrate les propriétés publiques si la clé existe, sinon garde la valeur par défaut
        foreach ($settings as $key => $value) {
            if (property_exists($this, $key)) {
                // Typage à la volée selon la valeur par défaut définie plus haut
                if (is_bool($this->{$key})) {
                    $this->{$key} = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } elseif (is_float($this->{$key})) {
                    $this->{$key} = (float)$value;
                } elseif (is_int($this->{$key})) {
                    $this->{$key} = (int)$value;
                } else {
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * Enregistre l'intégralité des configurations.
     */
    public function save()
    {
        $this->checkPermissionOrFail('parametres.modifier');
        $this->validate();

        // Récupère la liste des clés définies dans les règles de validation
        $keysToSave = array_keys($this->rules());

        foreach ($keysToSave as $key) {
            // Conversion propre des booléens en chaînes ('1' ou '0') pour le stockage TEXT
            $value = is_bool($this->{$key}) ? ($this->{$key} ? '1' : '0') : $this->{$key};

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        app(\App\Services\AppSettingsService::class)->clearCache();
        session()->flash('success', 'Configurations globales mises à jour avec succès.');
    }

    /**
     * Vérification de sécurité standard.
     */
    protected function checkPermissionOrFail(string $permission): bool
    {
        if (Gate::allows($permission)) {
            return true;
        }

        throw ValidationException::withMessages([
            'permission' => ["Action non autorisée : Privilèges insuffisants pour accéder aux paramètres."]
        ]);
    }

    public function render()
    {
        return view('livewire.users.settings');
    }
}
