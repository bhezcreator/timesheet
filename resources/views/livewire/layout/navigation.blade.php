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
            'title' => 'Permissions',
            'icon'  => 'la-key', // Modifié la-user -> la-key (sémantique claire)
            'route' => 'permissions.index',
        ],
        [
            'title' => 'Rôles',
            'icon'  => 'la-user-shield', // Modifié la-user -> la-user-shield (sécurité)
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

<nav x-data="{ open: false }" class="h-full bg-white border-gray-100 p-4">
    <!-- Desktop Navigation -->
    <div class="hidden lg:flex flex-col h-full justify-between">
        <!-- Liens du menu principal -->
        <div class="space-y-1.5">
            @foreach($menus as $menu)
                @php
                    // Extrait le premier mot de la route (ex: "users" depuis "users.index" ou "users.edit")
                    $routeGroup = $menu['route'] !== '#' ? explode('.', $menu['route'])[0] . '.*' : null;

                    // Le menu est actif si la route exacte correspond OU si on est dans le même groupe de sous-pages
                    $isActive = $menu['route'] !== '#' && (request()->routeIs($menu['route']) || ($routeGroup && request()->routeIs($routeGroup)));
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
                    {{-- CORRECTION : $active remplacé par $isActive ici --}}
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
