<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class NotificationsIndex extends Component
{
    use WithPagination;

    public $user;

    public $filter = 'all'; // all, unread, read

    public $perPage = 5;

    public $search = '';

    public $notificationCount = 0;

    protected $queryString = [
        'filter' => ['except' => 'all'],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'refreshNotifications' => '$refresh',
        'notificationRead' => 'handleNotificationRead',
    ];

    public function mount()
    {
        $this->user = User::find(Auth::id());
        $this->notificationCount = $this->user->unreadNotifications()->count();
    }

    public function getNotificationsProperty()
    {
        $query = $this->user->notifications();

        // Filtre par type
        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Recherche
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('data->title', 'LIKE', '%'.$this->search.'%')
                    ->orWhere('data->message', 'LIKE', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function readSingle($id)
    {
        $notification = $this->user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();

            // Mettre à jour le compteur
            $this->notificationCount = $this->user->unreadNotifications()->count();

            // Réinitialiser le cache
            $this->user->refresh();

            // Dispatch d'événement
            $this->dispatch('notificationRead', id: $notification->id);

            // Redirection avec JavaScript
            $this->js("setTimeout(() => {
                window.location.href = '".($notification->data['route_url'] ?? '#')."';
            }, 300)");
        }
    }

    public function markAllAsRead()
    {
        $this->user->unreadNotifications()->update(['read_at' => now()]);
        $this->notificationCount = 0;
        $this->dispatch('notificationsUpdated');

        session()->flash('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    public function deleteNotification($id)
    {
        $notification = $this->user->notifications()->find($id);

        if ($notification) {
            $notification->delete();
            $this->notificationCount = $this->user->unreadNotifications()->count();
            $this->dispatch('notificationsUpdated');

            session()->flash('success', 'Notification supprimée avec succès.');
        }
    }

    public function deleteAllRead()
    {
        $this->user->notifications()->whereNotNull('read_at')->delete();
        $this->dispatch('notificationsUpdated');

        $this->js("toastr.success('Toutes les notifications lues ont été supprimées')");
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function handleNotificationRead($id)
    {
        $this->notificationCount = $this->user->unreadNotifications()->count();
    }

    public function render()
    {
        return view('livewire.notifications-index', [
            'notifications' => $this->notifications,
        ]);
    }
}
