<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public int $userId;
    public User $user;

    public function mount(int $userId)
    {
        $this->userId = $userId;

        // Eager Loading des relations pour éviter le problème des requêtes N+1
        $this->user = User::with([
            'supervisor',
            'subordinates',
            'projects',
            'subProjects',
            'monthlyReports'
        ])->findOrFail($userId);
    }

    public function render()
    {
        return view('livewire.users.show');
    }
}
