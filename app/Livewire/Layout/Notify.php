<?php

namespace App\Livewire\Layout;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notify extends Component
{
    public User $user;

    public function mount()
    {
        $this->user = Auth::user();
    }

    /**
     * Écoute simultanément le canal des notifications standard et l'événement universel
     */
    public function getListeners()
    {
        $userId = $this->user->id;

        return [
            // Gardez seulement celui-ci
            "echo:private-App.Models.User.{$userId},UniversalModelStatusChanged" => 'playAlertSound',
        ];
    }

    public function markAllAsRead()
    {
        $this->user->unreadNotifications->markAsRead();
        // Recharger les données utilisateur
        $this->user->refresh();
        $this->user->load('unreadNotifications');
        $this->dispatch('$refresh');
    }

    public function readSingle($id)
    {
        $notification = $this->user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();

            // Recharger les données utilisateur
            $this->user->refresh();
            $this->user->load('unreadNotifications');
            $this->dispatch('$refresh');

            // Petite pause pour laisser Livewire se mettre à jour
            // puis rediriger via JavaScript
            $this->js("setTimeout(() => { window.location.href = '" . ($notification->data['route_url'] ?? '#') . "'; }, 100)");
        }
    }

    /**
     * Déclenche le signal sonore et rafraîchit l'affichage du HTML
     */
    public function playAlertSound($event = null)
    {
        // Rafraîchir les données de l'utilisateur
        $this->user->refresh();

        // Jouer le son
        $this->dispatch('play-notification-sound');

        // Rafraîchir le composant
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.layout.notify');
    }
}
