<?php

namespace App\Livewire\Users;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SettingsTabs extends Component
{
    // Onglet actif par défaut
    public string $activeTab = 'general';

    // Liste des onglets disponibles
    public array $tabs = [
        'general'  => ['label' => 'Paramètres généraux', 'icon' => 'la-sliders-h'],
        'profile'  => ['label' => 'Profil', 'icon' => 'la-user-tie'],
        'security' => ['label' => 'Sécurité', 'icon' => 'la-shield-alt'],
    ];

    public function changeTab(string $tabName)
    {
        if (array_key_exists($tabName, $this->tabs)) {
            $this->activeTab = $tabName;
        }
    }

    public function render()
    {
        return view('livewire.users.settings-tabs');
    }
}
