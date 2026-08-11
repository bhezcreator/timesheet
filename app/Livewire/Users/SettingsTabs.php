<?php

namespace App\Livewire\Users;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class SettingsTabs extends Component
{
    #[Url(as: 'tab', keep: true)]
    public string $activeTab = 'profile';

    // Configuration des onglets avec permissions
    public array $tabs = [
        'profile' => [
            'label' => 'Profil',
            'icon' => 'la-user-tie',
            'permission' => null, // Accessible à tous
        ],
        'signature' => [
            'label' => 'Signature',
            'icon' => 'la-signature',
            'permission' => null,
        ],
        'type' => [
            'label' => 'Type activités',
            'icon' => 'la-tags',
            'permission' => 'types_activites.voir',
        ],
        'blockedDay' => [
            'label' => 'Jours verrouillés',
            'icon' => 'la-calendar-times',
            'permission' => 'jour_bloque.voir',
        ],
        'general' => [
            'label' => 'Paramètres généraux',
            'icon' => 'la-sliders-h',
            'permission' => 'parametres_general',
        ],
        'logs' => [
            'label' => 'Journal d\'activité',
            'icon' => 'la-history',
            'permission' => 'journaux.voir',
        ],
    ];

    public array $filteredTabs = [];

    public function mount(): void
    {
        $this->filteredTabs = $this->getFilteredTabs();

        // Vérifier si l'onglet actif est toujours accessible
        if (!array_key_exists($this->activeTab, $this->filteredTabs)) {
            // Rediriger vers le premier onglet accessible
            $firstTab = array_key_first($this->filteredTabs);
            $this->activeTab = $firstTab ?: 'profile';
        }
    }

    /**
     * Get filtered tabs based on user permissions
     */

    protected function getFilteredTabs(): array
    {
        $user = Auth::user();

        if (!$user) {
            return $this->getPublicTabs();
        }

        return array_filter($this->tabs, function ($tab) use ($user) {
            if (empty($tab['permission'])) {
                return true;
            }

            try {
                // Utiliser Gate au lieu de can()
                return Gate::forUser($user)->allows($tab['permission']);
            } catch (\Exception $e) {
                Log::warning('Erreur de vérification de permission', [
                    'permission' => $tab['permission'],
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                return false;
            }
        });
    }

    /**
     * Get public tabs (accessible without login)
     */
    protected function getPublicTabs(): array
    {
        return array_filter($this->tabs, function ($tab) {
            return empty($tab['permission']);
        });
    }

    public function changeTab(string $tabName): void
    {
        if (array_key_exists($tabName, $this->filteredTabs)) {
            $this->activeTab = $tabName;
        }
    }

    public function render()
    {
        return view('livewire.users.settings-tabs', [
            'tabs' => $this->filteredTabs,
        ]);
    }
}
