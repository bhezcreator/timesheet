<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profil extends Component
{
    use WithFileUploads;

    // Utilisateur connecté
    public User $user;

    // Champs du formulaire
    public string $num_order = '';

    public string $name = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $job_title = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $notification_database = true;

    public bool $notification_email = false;

    // Fichier temporaire pour la photo
    public $photo;

    public function mount()
    {
        // Récupération de l'utilisateur connecté en ligne
        $this->user = User::find(Auth::id());

        // Initialisation des champs
        $this->num_order = $this->user->num_order ?? '';
        $this->name = $this->user->name ?? '';
        $this->first_name = $this->user->first_name ?? '';
        $this->last_name = $this->user->last_name ?? '';
        $this->job_title = $this->user->job_title ?? '';
        $this->email = $this->user->email ?? '';

        $this->notification_database = $this->user->settings['notifications']['database'] ?? false;
        $this->notification_email = $this->user->settings['notifications']['email'] ?? false;
    }

    protected function rules(): array
    {
        return [
            'num_order' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'],
            'photo' => ['nullable', 'image', 'max:2048'], // Max 2MB
        ];
    }

    /**
     * Messages d'erreur personnalisés et compréhensibles.
     */
    protected function messages(): array
    {
        return [
            // Messages génériques
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit être du texte.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'email' => 'L’adresse email saisie n’est pas valide.',

            // Spécifique à l'unicité de l'email
            'email.unique' => 'Cette adresse email est déjà enregistrée pour un autre utilisateur.',

            // Spécifiques au mot de passe
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'password.confirmed' => 'Les deux mots de passe saisis ne correspondent pas.',
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial (@, $, !, %, *, ?, &).',

            // Spécifiques à la photo
            'photo.image' => 'Le fichier téléversé doit être une image (jpg, png, jpeg, etc.).',
            'photo.max' => 'L’image est trop lourde. Elle ne doit pas dépasser 2 Mo (2048 Ko).',
        ];
    }

    /**
     * Traduction des noms des champs informatiques.
     */
    protected function validationAttributes(): array
    {
        return [
            'num_order' => 'numéro d’ordre',
            'name' => 'nom complet',
            'first_name' => 'prénom',
            'last_name' => 'nom de famille',
            'job_title' => 'titre du poste',
            'email' => 'adresse email',
            'password' => 'mot de passe',
            'photo' => 'photo de profil',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'num_order' => trim($this->num_order) ?: null,
            'name' => trim($this->name),
            'first_name' => trim($this->first_name),
            'last_name' => trim($this->last_name) ?: null,
            'job_title' => trim($this->job_title) ?: null,
            'email' => trim($this->email),
            'settings' => [
                'notifications' => [
                    'database' => $this->notification_database ?: false,
                    'email' => $this->notification_email ?: false,
                ],
            ],
        ];

        // Traitement de la photo si un nouveau fichier est chargé
        if ($this->photo) {
            $path = $this->photo->store('photos', 'public');
            $data['photo'] = $path;
        }

        // Mise à jour du mot de passe uniquement s'il est renseigné
        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $this->user->update($data);

        // Réinitialisation des champs sensibles de mot de passe
        $this->reset(['password', 'password_confirmation', 'photo']);

        session()->flash('success', 'Votre profil a été mis à jour avec succès.');
    }

    public function render()
    {
        return view('livewire.users.profil');
    }
}
