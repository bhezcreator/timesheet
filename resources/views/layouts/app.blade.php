<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">

        <title>
            {{ config('app.name', 'TimeSheet') }}
        </title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
            rel="stylesheet" />

        <!-- Assets -->
        @vite([
            'resources/css/app.css',
            'resources/js/app.js'
        ])

        @livewireStyles
    </head>

    <body class="font-sans antialiased bg-gray-50 text-gray-900 h-screen overflow-hidden">

        <div class="min-h-screen flex flex-col lg:flex-row">
            <!-- Sidebar -->
            <aside class="hidden lg:flex lg:w-72 lg:flex-col lg:flex-shrink-0 bg-white border-r border-gray-200">
                <div class="h-20 flex items-center px-4 sm:px-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 sm:gap-3">
                        <!-- Logo CNRSC -->
                        <div class="w-14 h-14 rounded-xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/logo.jpg') }}"
                                alt="Logo CNRSC"
                                class="w-full h-full object-cover">
                        </div>

                        <div>
                            <h1 class="font-bold text-lg sm:text-xl">
                                CNRSC ASBL
                            </h1>
                            <p class="text-[10px] sm:text-xs text-gray-500">
                                Gestion intelligente du temps
                            </p>
                        </div>
                    </a>
                </div>

                <div class="flex-1 px-3 sm:px-4 py-4 sm:py-6 overflow-y-auto">
                    <livewire:layout.navigation />
                </div>
            </aside>

            <!-- Main Area -->
            <div class="flex-1 flex flex-col min-h-screen lg:min-h-0">
                <!-- Header -->
                <header class="h-16 sm:h-20 bg-white border-b border-gray-200 flex items-center justify-between px-3 sm:px-6 flex-shrink-0">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <livewire:layout.mobile-menu />

                        <div class="hidden md:block min-w-0">
                            @if(isset($header))
                                <div class="truncate">
                                    {{ $header }}
                                </div>
                            @else
                                <h2 class="text-base sm:text-xl font-semibold truncate">
                                    Tableau de bord
                                </h2>
                            @endif
                        </div>
                    </div>

                    <!-- Notification et Profil Utilisateur -->
                    <div class="flex-shrink-0">
                        <livewire:layout.notify />
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 min-h-0">
                    <div class="w-full p-3 sm:p-6 overflow-y-auto h-[calc(100vh-64px)] sm:h-[calc(100vh-80px)] relative">

                        <!-- Écran de chargement (Overlay) Livewire -->
                        <div wire:loading class="absolute inset-0 bg-gray-900/10 backdrop-blur-[2px] z-50 flex items-center justify-center transition-all">
                            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-xl flex flex-col items-center border border-gray-100 min-w-[120px] sm:min-w-[160px]">
                                <x-ui.spinner size="lg" class="text-blue-600" />
                                <p class="mt-2 sm:mt-3 text-xs sm:text-sm font-medium text-gray-600 tracking-wide">
                                    Chargement...
                                </p>
                            </div>
                        </div>

                        <!-- Contenu dynamique -->
                        <div class="w-full">
                            {{ $slot }}
                        </div>

                    </div>
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
