<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
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
            'route' => 'projects.index',
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
            'title' => 'Personnels',
            'icon'  => 'la-users',
            'route' => 'users.index',
        ],
        [
            'title' => 'Permission',
            'icon'  => 'la-user',
            'route' => 'permissions.index',
        ],
                [
            'title' => 'Roles',
            'icon'  => 'la-user',
            'route' => 'roles.index',
        ],
    ];

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open:false }" class="h-full">

    <!-- Desktop Navigation -->

    <div class="hidden lg:flex flex-col h-full">

        <div class="space-y-2">

            @foreach($menus as $menu)

                @php
                    $active = $menu['route'] !== '#' && request()->routeIs($menu['route']);
                @endphp

                <a
                    href="{{ $menu['route'] === '#' ? '#' : route($menu['route']) }}"
                    @if($menu['route'] !== '#') wire:navigate @endif

                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200

                    {{ $active
                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20'
                        : 'text-gray-600 hover:bg-gray-100 hover:text-indigo-600'
                    }}"
                >

                    <i class="las {{ $menu['icon'] }} text-xl"></i>

                    <span class="font-medium">

                        {{ $menu['title'] }}

                    </span>

                </a>

            @endforeach

        </div>
        <div class="mt-auto pt-3 border-t border-gray-200">
            <button
                wire:click="logout"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition cursor-pointer"
            >
                <i class="las la-sign-out-alt text-xl"></i>
                Déconnexion
            </button>
        </div>
    </div>
</nav>
