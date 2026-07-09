<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
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

    <body class="font-sans antialiased bg-gray-50 text-gray-900">

        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside class="hidden lg:flex lg:w-72 lg:flex-col bg-white border-r border-gray-200">
                <div class="h-20 flex items-center px-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-lg">
                            <i class="las la-clock text-3xl"></i>
                        </div>

                        <div>

                            <h1 class="font-bold text-xl">
                                Timesheet
                            </h1>

                            <p class="text-xs text-gray-500">
                                Timesheet Manager
                            </p>

                        </div>
                    </a>
                </div>

                <div class="flex-1 px-4 py-6">
                    <livewire:layout.navigation />
                </div>
            </aside>
            <!-- Main Area -->

            <div class="flex-1 flex flex-col">
                <!-- Header -->
                <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-6">
                    <livewire:layout.mobile-menu />
                    <div>
                        @if(isset($header))

                            {{ $header }}

                        @else
                            <h2 class="text-xl font-semibold">
                                Tableau de bord
                            </h2>
                        @endif
                    </div>

                    <div class="flex items-center gap-4">
                        <button class="relative text-gray-500 hover:text-indigo-600">
                            <i class="las la-bell text-2xl"></i>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                        </button>

                        <div class="flex items-center gap-3">


                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">


                                <i class="las la-user text-indigo-600 text-xl"></i>


                            </div>



                            <div class="hidden md:block">


                                <p class="text-sm font-semibold">

                                    {{ auth()->user()->name ?? 'Utilisateur' }}

                                </p>


                                <p class="text-xs text-gray-500">

                                    Administrateur

                                </p>


                            </div>

                        </div>
                    </div>

                </header>

                <!-- Page Content -->

                <main class="flex-1 p-6 overflow-y-auto">
                    <div class="w-full h-full">

                        {{ $slot }}

                    </div>
                </main>
            </div>

        </div>

        @livewireScripts
    </body>
</html>
