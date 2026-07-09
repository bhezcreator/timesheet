<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class MobileMenu extends Component
{

    public array $menus = [
        [
            'title' => 'Tableau de bord',
            'icon'  => 'la-home',
            'route' => 'dashboard',
        ],
        [
            'title' => 'Feuilles de temps',
            'icon'  => 'la-business-time',
            'route' => '#',
        ],
        [
            'title' => 'Projets',
            'icon'  => 'la-project-diagram',
            'route' => '#',
        ],
        [
            'title' => 'Activités',
            'icon'  => 'la-tasks',
            'route' => '#',
        ],
        [
            'title' => 'Rapports',
            'icon'  => 'la-chart-bar',
            'route' => '#',
        ],
        [
            'title' => 'Utilisateurs',
            'icon'  => 'la-users',
            'route' => '#',
        ],
        [
            'title' => 'Profil',
            'icon'  => 'la-user',
            'route' => 'profile',
        ],
    ];

    public function render()
    {
        return view('livewire.layout.mobile-menu');
    }
}
