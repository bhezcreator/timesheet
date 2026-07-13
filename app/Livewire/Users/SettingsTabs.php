<?php

namespace App\Livewire\Users;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url; // 1. IMPORTATION INDISPENSABLE
use Livewire\Component;

#[Layout('layouts.app')]
class SettingsTabs extends Component
{
    // 2. AJOUT DE L'ATTRIBUT POUR PERSISTER LA DONNÉE DANS L'URL
    #[Url(as: 'tab', keep: true)]
    public string $activeTab = 'profile';

    // Liste des onglets disponibles
    public array $tabs = [
        'profile'  => ['label' => 'Profil', 'icon' => 'la-user-tie'],
        'capture' => ['label' => 'Capture', 'icon' => 'la-camera'],
        'type'  => ['label' => 'Type activité', 'icon' => 'la-tags'],
        'general'  => ['label' => 'Paramètres généraux', 'icon' => 'la-sliders-h'],
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
