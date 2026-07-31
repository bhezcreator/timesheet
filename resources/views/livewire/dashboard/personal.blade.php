<div class="p-4 border border-gray-100 bg-white rounded-xl shadow-sm border-t border-t-blue-700">
    <!-- En-tête avec bienvenue -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Tableau de bord personnel
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Bienvenue {{ $user->name ?? 'Utilisateur' }} ! Voici votre résumé d'activité
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button
                wire:click="refresh"
                wire:loading.attr="disabled"
                class="inline-flex items-center cursor-pointer px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <svg wire:loading.remove class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Actualiser
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
        <!-- Cartes des statistiques -->

        <!-- Activités du jour -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Activités du jour</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $todayActivities }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center text-xs text-gray-500">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ now()->format('d/m/Y') }}
            </div>
        </div>

        <!-- Activités du mois -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Activités du mois</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $monthActivities }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center text-xs text-gray-500">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ now()->translatedFormat('F Y') }}

            </div>
        </div>

        <!-- Total d'heures -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Total d'heures</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalHours, 1) }}</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center text-xs text-gray-500">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Ce mois-ci
            </div>
        </div>

        <!-- Rapports soumis -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Rapports soumis</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $submittedReports }}</p>
                </div>
                <div class="p-3 bg-emerald-100 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center text-xs text-gray-500">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    Approuvés & Soumis
                </span>
            </div>
        </div>

        <!-- Rapports en attente -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Rapports en attente</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $pendingReports }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center text-xs text-gray-500">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    En attente de validation
                </span>
            </div>
        </div>

                <!-- Utilisateurs en ligne - Visible uniquement pour les admins -->
        @if($user?->getRoleNames()->first() === 'Admin')
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">Utilisateurs en ligne</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalUsers }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center text-xs text-gray-500">
                    <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                    Actifs maintenant
                </div>
            </div>
        @endif
    </div>

    <!-- Section supplémentaire - Activités récentes (optionnelle) -->
    <div class="mt-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Activités récentes</h2>
            </div>
            <div class="p-6 text-center text-gray-500">
                <p class="text-sm">Vous avez {{ $todayActivities }} activité(s) aujourd'hui et {{ $monthActivities }} ce mois-ci</p>
                <div class="mt-4 flex justify-center space-x-4">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $todayActivities }} aujourd'hui
                    </div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        {{ $monthActivities }} ce mois
                    </div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                        {{ number_format($totalHours, 1) }} heures
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification de mise à jour -->
    <div
        x-data="{ show: false }"
        x-on:statistics-updated.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition:enter.duration.300ms
        x-transition:leave.duration.300ms
        class="fixed bottom-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"
    >
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Statistiques mises à jour
        </div>
    </div>
</div>
