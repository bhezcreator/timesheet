<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class MobileMenu extends Component
{
    public array $menus = [
        [
            'title' => 'Tableau de bord',
            'icon'  => 'la-home',
            'route' => 'dashboard',
            'permission' => null, // Accessible à tous
        ],
        [
            'title' => 'Feuilles de temps',
            'icon'  => 'la-business-time',
            'route' => 'timesheet.calendar',
            'permission' => 'projets.voir',
        ],
        [
            'title' => 'Projets',
            'icon'  => 'la-project-diagram',
            'route' => 'projects.index',
            'permission' => 'projets.voir',
        ],
        [
            'title' => 'Activités',
            'icon'  => 'la-tasks',
            'route' => 'activities.index',
            'permission' => 'activites.voir',
        ],
        [
            'title' => 'Rapports',
            'icon'  => 'la-chart-bar',
            'route' => 'rapports.index',
            'permission' => 'rapports.voir',
        ],
        [
            'title' => 'Liste des rapports',
            'icon'  => 'la-list',
            'route' => 'reports.index',
            'permission' => 'Voir_liste_rapport',
        ],
        [
            'title' => 'Validations',
            'icon'  => 'la-check',
            'route' => 'validations.supervisor',
            'permission' => 'validations.voir',
        ],
        [
            'title' => 'Personnels',
            'icon'  => 'la-users',
            'route' => 'users.index',
            'permission' => 'utilisateurs.voir',
        ],
        // [
        //     'title' => 'Permissions',
        //     'icon'  => 'la-key',
        //     'route' => 'permissions.index',
        //     'permission' => 'permissions.voir',
        // ],
        [
            'title' => 'Rôles',
            'icon'  => 'la-user-shield',
            'route' => 'roles.index',
            'permission' => 'roles.voir',
        ],
        [
            'title' => 'Paramètres',
            'icon'  => 'la-cog',
            'route' => 'settings',
            'permission' => 'parametres.voir',
        ],
    ];

    public array $filteredMenus = [];

    /**
     * Mount method - Initialize component
     */
    public function mount(): void
    {
        $this->filteredMenus = $this->getFilteredMenus();
    }

    /**
     * Get filtered menus based on user permissions
     */

    protected function getFilteredMenus(): array
    {
        $user = Auth::user();

        if (!$user) {
            return array_filter($this->menus, function ($menu) {
                return empty($menu['permission']);
            });
        }

        return array_filter($this->menus, function ($menu) use ($user) {
            if (empty($menu['permission'])) {
                return true;
            }

            // Utiliser Gate::allows() au lieu de can()
            return Gate::forUser($user)->allows($menu['permission']);
        });
    }

    /**
     * Check if a menu item is active
     */
    protected function isMenuActive(array $menu): bool
    {
        if ($menu['route'] === '#') {
            return false;
        }

        $routeGroup = explode('.', $menu['route'])[0] . '.*';

        return request()->routeIs($menu['route']) || request()->routeIs($routeGroup);
    }

    public function render()
    {
        return view('livewire.layout.mobile-menu', [
            'menus' => $this->filteredMenus,
        ]);
    }
}
