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
            'route' => 'timesheet.calendar',
        ],
        [
            'title' => 'Projets',
            'icon'  => 'la-project-diagram',
            'route' => 'projects.index',
        ],
        [
            'title' => 'Activités',
            'icon'  => 'la-tasks',
            'route' => 'activities.index',
        ],
        [
            'title' => 'Rapports',
            'icon'  => 'la-chart-bar',
            'route' => 'rapports.index',
        ],

        [
            'title' => 'Liste Rapport',
            'icon'  => 'la-list',
            'route' => 'reports.index',
        ],
        [
            'title' => 'Validations',
            'icon'  => 'la-check',
            'route' => 'validations.supervisor',
        ],
        [
            'title' => 'Personnels',
            'icon'  => 'la-users',
            'route' => 'users.index',
        ],
        [
            'title' => 'Permissions',
            'icon'  => 'la-key', // Modifié la-user -> la-key (sémantique claire)
            'route' => 'permissions.index',
        ],
        [
            'title' => 'Rôles',
            'icon'  => 'la-user-shield', // Modifié la-user -> la-user-shield (sécurité)
            'route' => 'roles.index',
        ],
        [
            'title' => 'Paramètres',
            'icon'  => 'la-cog',
            'route' => 'settings',
        ],
    ];
    public function render()
    {
        return view('livewire.layout.mobile-menu');
    }
}
