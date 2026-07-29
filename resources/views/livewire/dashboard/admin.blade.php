<div class="p-0 mt-8">
    @if(!$hasPermission)
        <!-- Page d'erreur 403 -->
        <div class="min-h-[600px] flex items-center justify-center">
            <div class="text-center max-w-2xl mx-auto px-4">
                <div class="mb-8 relative">
                    <div class="w-32 h-32 mx-auto bg-red-50 rounded-full flex items-center justify-center animate-pulse">
                        <svg class="w-20 h-20 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="absolute -top-2 -right-2 animate-bounce">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            ⚠️ 403
                        </span>
                    </div>
                </div>

                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Accès Refusé
                </h1>
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
                    <p class="text-red-700 text-lg font-medium">
                        {{ $errorMessage ?? 'Vous n\'avez pas les permissions nécessaires pour accéder à ce tableau de bord.' }}
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 mb-8 text-left">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-gray-600">
                            <p class="font-medium text-gray-700">Informations :</p>
                            <ul class="mt-1 space-y-1 list-disc list-inside">
                                <li>Vous devez avoir la permission <span class="font-mono text-xs bg-gray-200 px-2 py-0.5 rounded">tableauAdmin</span></li>
                                <li>Rôle requis : <span class="font-medium text-purple-600">Administrateur</span></li>
                                <li>Contactez votre administrateur si vous pensez avoir besoin de ces droits</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Retour au tableau de bord
                    </a>
                </div>

                <p class="mt-8 text-xs text-gray-400">
                    Code d'erreur : ERR_ACCESS_DENIED_403 • Référence : {{ uniqid() }}
                </p>
            </div>
        </div>
    @else
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <p class="mt-1 text-sm text-gray-600">
                    Vue d'ensemble de la plateforme
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ $totalUsers }} utilisateurs
                </span>
                <button
                    wire:click="refresh"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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

        <!-- Cartes de statistiques principales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
            <!-- Projets actifs -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">Projets actifs</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $activeProjects }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center text-xs text-gray-500">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $totalProjects }} total
                    </span>
                    <span class="ml-2">{{ $completedProjects }} terminés</span>
                </div>
            </div>

            <!-- Sous-projets -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">Sous-projets</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalSubProjects }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center text-xs text-gray-500">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        {{ $activeSubProjects }} actifs
                    </span>
                </div>
            </div>

            <!-- Statistiques globales - Activités -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">Activités totales</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalActivities) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center text-xs text-gray-500">
                    <span>{{ $totalActivitiesMonth }} ce mois</span>
                    <span class="mx-1">•</span>
                    <span>{{ number_format($totalHours, 1) }} heures totales</span>
                </div>
            </div>

            <!-- Taux de validation -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">Taux de validation</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $validationRate }}%</p>
                    </div>
                    <div class="p-3 bg-emerald-100 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $validationRate }}%"></div>
                    </div>
                    <div class="mt-1 flex justify-between text-xs text-gray-500">
                        <span>{{ $pendingReports }} en attente</span>
                        <span>{{ $totalReports }} rapports</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques détaillées -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Projets</p>
                        <p class="text-xl font-bold text-gray-900">{{ $totalProjects }}</p>
                    </div>
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Sous-projets</p>
                        <p class="text-xl font-bold text-gray-900">{{ $totalSubProjects }}</p>
                    </div>
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Utilisateurs</p>
                        <p class="text-xl font-bold text-gray-900">{{ $totalUsers }}</p>
                    </div>
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Rapports en attente</p>
                        <p class="text-xl font-bold text-gray-900">{{ $pendingReports }}</p>
                    </div>
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Projets et Activités par mois -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top Projets -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Top Projets les plus actifs
                    </h2>
                </div>
                <div class="p-4 max-h-80 overflow-y-auto">
                    @forelse($topProjects as $project)
                        <div class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-gray-50 px-2 rounded-lg transition-colors">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800">{{ $project['name'] }}</p>
                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                    <span class="font-mono text-xs">{{ $project['code'] }}</span>
                                    <span class="mx-1">•</span>
                                    <span>{{ $project['activities_count'] }} activités</span>
                                    <span class="mx-1">•</span>
                                    <span>{{ $project['total_hours'] }}h</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $project['status'] === 'actif' ? 'bg-green-100 text-green-800' :
                                   ($project['status'] === 'terminé' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $project['status'] }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p>Aucun projet trouvé</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Activités par mois -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Activités par mois ({{ Carbon\Carbon::now()->year }})
                    </h2>
                </div>
                <div class="p-4">
                    @if(!empty($activitiesByMonth))
                        <div class="space-y-3">
                            @foreach($activitiesByMonth as $monthData)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600">{{ $monthData['month'] }}</span>
                                        <span class="font-medium text-gray-700">{{ $monthData['total'] }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        @php
                                            $maxValue = collect($activitiesByMonth)->max('total');
                                            $percentage = $maxValue > 0 ? ($monthData['total'] / $maxValue) * 100 : 0;
                                        @endphp
                                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2.5 rounded-full transition-all duration-500"
                                             style="width: {{ $percentage }}%">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>Aucune activité enregistrée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Projets récents -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Projets récents
                </h2>
                <span class="text-sm text-gray-500">{{ count($recentProjects) }} projets</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsable</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sous-projets</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentProjects as $project)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $project['name'] }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $project['code'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $project['manager_name'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $project['sub_projects_count'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $project['status'] === 'actif' ? 'bg-green-100 text-green-800' :
                                           ($project['status'] === 'terminé' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $project['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $project['created_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <p>Aucun projet trouvé</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
    @endif
</div>
