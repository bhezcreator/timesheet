<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class SignatureCapture extends Component
{
    use WithFileUploads;
    // Reçoit les données de l'image au format Base64 depuis le Javascript
    public ?string $signatureData = null;
    // Variable temporaire pour le fichier importé
    public $signatureFile = null;

    public $user;

    // Propriété à ajouter en haut du composant
    public bool $showSignaturePad = false;

    public function mount()
    {
        $this->user = User::find(Auth::id());
    }

    public function saveSignature()
    {
        // 1. Validation de la présence du tracé
        if (empty($this->signatureData)) {
            $this->dispatch('notify', type: 'error', message: 'Le cadre de signature est vide.');
            return;
        }

        // 2. Extraction et décodage de la chaîne Base64 (ex: "data:image/png;base64,iVBORw0K...")
        try {
            $imageInfo = explode(";base64,", $this->signatureData);
            $imageType = explode("image/", $imageInfo[0])[1]; // png, jpeg, etc.
            $base64Image = base64_decode($imageInfo[1]);

            // 3. Génération d'un nom unique pour le fichier
            $fileName = 'signatures/' . Str::random(40) . '.' . $imageType;

            // 4. Stockage physique du fichier sur le disque public (équivalent à store())
            Storage::disk('public')->put($fileName, $base64Image);

            // 5. Sauvegarde du chemin en base de données pour l'utilisateur connecté
            $this->user->update([
                'signature' => $fileName
            ]);

            session()->flash('success', 'Votre signature électronique a été enregistrée avec succès.');
            // Réinitialisation
            $this->reset('signatureData');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors du traitement de la signature : ' . $e->getMessage());
        }
    }

    /**
     * Permet d'afficher ou masquer la zone de dessin
     */
    public function toggleSignaturePad()
    {
        $this->showSignaturePad = !$this->showSignaturePad;
    }

    /**
     * Hook magique de Livewire : se déclenche automatiquement
     * dès que le téléversement du fichier est achevé avec succès.
     */
    public function updatedSignatureFile()
    {
        $this->saveSignatureFromFile();
    }

    /**
     * Traite et enregistre la signature chargée depuis un fichier local
     */
    public function saveSignatureFromFile()
    {
        // Votre validation stricte reste inchangée
        $this->validate([
            'signatureFile' => ['required', 'image', 'max:2048', 'mimes:png,jpg,jpeg'],
        ]);

        try {
            // Stockage physique
            $path = $this->signatureFile->store('signatures', 'public');

            // Mise à jour de l'utilisateur
            $this->user->update([
                'signature' => $path
            ]);

            // Nettoyage de la variable temporaire
            $this->reset('signatureFile');
            session()->flash('success', 'Votre signature a été importée et enregistrée avec succès.');

            $this->showSignaturePad = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'enregistrement du fichier : ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.users.signature-capture');
    }
}
