<?php

namespace App\Livewire\Layout;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notify extends Component
{
    /**
     * Génère dynamiquement l'écouteur d'événement pour l'utilisateur connecté
     */
    public function getListeners()
    {
        $userId = $this->user->id;

        return [
            "echo:private-App.Models.User.{$userId},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'playAlertSound',
        ];
    }

    public User $user;

    public function mount()
    {
        $this->user = Auth::user();
    }
    /**
     * Marquer toutes les notifications de l'utilisateur connecté comme lues.
     */
    public function markAllAsRead()
    {
        $this->user->unreadNotifications->markAsRead();

        // Rafraîchit l'état local de ce composant spécifique
        $this->dispatch('$refresh');
    }

    /**
     * Marque une notification spécifique comme lue et redirige l'utilisateur
     */
    public function readSingle($id)
    {
        $notification = $this->user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return redirect()->to($notification->data['route_url'] ?? '#');
        }
    }

    /**
     * Déclenche l'événement sonore côté navigateur
     */
    public function playAlertSound()
    {
        $this->dispatch('play-notification-sound');
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.layout.notify');
    }
}
