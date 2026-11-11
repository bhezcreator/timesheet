<?php

namespace App\Livewire;

use Livewire\Component;

class QrScanner extends Component
{
    // Cette méthode sera appelée dès qu'un QR code sera détecté par la caméra
    public function processScan($qrContent)
    {
        // Exemple : Logique de traitement (recherche d'une activité, validation, etc.)
        // $this->dispatch('notify', 'Code détecté : ' . $qrContent);
        // dd($qrContent);  Pour le moment, on fait juste un dump du contenu du QR code
        session()->flash('message', 'QR Code lu avec succès : ' . $qrContent);
    }

    public function render()
    {
        return view('livewire.qr-scanner');
    }
}
