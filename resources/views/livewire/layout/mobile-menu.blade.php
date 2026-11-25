<div x-data="{ open: false }" class="lg:hidden">
    <!-- Bouton Hamburger -->
    <button @click="open = true" class="p-3 rounded-xl text-gray-600 hover:bg-gray-100 transition cursor-pointer">
        <i class="las la-bars text-2xl"></i>
    </button>

    <!-- Overlay -->
    <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 bg-black/40 z-40" x-cloak></div>

    <!-- Menu Mobile -->
    <aside x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed top-0 left-0 z-50 w-72 h-screen bg-white shadow-xl flex flex-col"
        x-cloak>

        <!-- Header Menu -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                 <a href="{{ route('dashboard') }}" class="flex items-center gap-2 sm:gap-3">
                    <!-- Logo CNRSC -->
                    <div class="w-14 h-14 rounded-xl overflow-hidden shadow-md">
                        <img src="{{ asset('images/logo.jpg') }}"
                            alt="Logo CNRSC"
                            class="w-full h-full object-cover">
                    </div>

                    <div>
                        <h1 class="font-bold text-lg sm:text-xl text-black-800">
                            CNRSC <span class="text-red-600 font-light">ASBL</span>
                        </h1>
                        <p class="text-[10px] sm:text-xs text-gray-500">
                            Gestion intelligente du temps
                        </p>
                    </div>
                </a>
            </div>

            <!-- Close -->
            <button @click="open = false" class="cursor-pointer text-gray-500 hover:text-red-500 transition-colors">
                <i class="las la-times text-2xl"></i>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div class="flex-1 overflow-y-auto p-4 flex flex-col justify-between">

            <!-- Liens du menu principal -->
            <div class="space-y-1.5">
                @foreach($filteredMenus as $menu)
                    @php
                        $isActive = $this->isMenuActive($menu);
                    @endphp

                    <a
                        href="{{ $menu['route'] === '#' ? '#' : route($menu['route']) }}"
                        @if($menu['route'] !== '#') wire:navigate @endif
                        @click="open = false"
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

        </div>

        <!-- Logout -->
        <div class="mt-auto p-4 border-t border-gray-100">
            <button wire:click="logout" @click="open = false" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition cursor-pointer font-medium">
                <i class="las la-sign-out-alt text-xl"></i>
                <span>Déconnexion</span>
            </button>
        </div>
    </aside>
</div>
