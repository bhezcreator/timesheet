<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
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
        [
            'title' => 'Permissions',
            'icon'  => 'la-key',
            'route' => 'permissions.index',
            'permission' => 'permissions.voir',
        ],
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

        // Si l'utilisateur n'est pas connecté, retourner les menus publics seulement
        if (!$user) {
            return array_filter($this->menus, function ($menu) {
                return empty($menu['permission']);
            });
        }

        // Filtrer les menus selon les permissions
        return array_filter($this->menus, function ($menu) use ($user) {
            // Si pas de permission requise, tout le monde voit
            if (empty($menu['permission'])) {
                return true;
            }

            // Vérifier si l'utilisateur a la permission
            return $user->can($menu['permission']);
        });
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
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
};
?>

<nav x-data="{ open: false }" class="h-full bg-white border-gray-100 p-0">
    <!-- Desktop Navigation -->
    <div class="hidden lg:flex flex-col h-full justify-between">
        <!-- Liens du menu principal -->
        <div class="space-y-1.5">
            @foreach($filteredMenus as $menu)
                @php
                    $isActive = $this->isMenuActive($menu);
                @endphp

                <a
                    href="{{ $menu['route'] === '#' ? '#' : route($menu['route']) }}"
                    @if($menu['route'] !== '#') wire:navigate @endif
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group font-medium text-sm
                    {{ $isActive
                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20 font-semibold'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600'
                    }}"
                >
                    <i class="las {{ $menu['icon'] }} text-xl transition-colors {{ $isActive ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}"></i>
                    <span>{{ $menu['title'] }}</span>
                </a>
            @endforeach
        </div>

        <!-- Pied du menu : Bouton Déconnexion -->
        <div class="pt-3 border-t border-gray-100">
            <button
                type="button"
                wire:click="logout"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50/70 transition cursor-pointer focus:outline-none"
            >
                <i class="las la-sign-out-alt text-xl"></i>
                <span>Déconnexion</span>
            </button>
        </div>
    </div>
</nav>
