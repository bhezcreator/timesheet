<div class="p-4 mt-8 border border-gray-100 bg-white rounded-xl shadow-sm border-t border-t-blue-700">
    @if(session('permission'))
        <x-ui.alert type="error" class="mb-4 mt-8">
            {{ session('permission') }}
        </x-ui.alert>
    @endif

    <!-- En-tête avec bienvenue -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <p class="mt-1 font-bold text[18px] text-gray-900">
            Gérez et suivez les activités de votre équipe
        </p>

        <div class="mt-4 sm:mt-0 flex space-x-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ count($teamMembers) }} membres
            </span>
        </div>
    </div>

    <!-- Messages Flash -->
    @if (session()->has('success'))
        <div class="mb-4 bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Cartes de statistiques principales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-8">
        <!-- Rapports à valider -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Rapports à valider</p>
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
                    {{ $pendingReports > 0 ? 'Action requise' : 'Tout est validé' }}
                </span>
                @if($pendingReports > 0)
                    <span class="ml-2 text-xs text-yellow-600 animate-pulse">● En attente</span>
                @endif
            </div>
        </div>

        <!-- Statistiques de l'équipe -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Statistiques de l'équipe</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $teamStats['total_activities'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Activités ce mois</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center text-xs text-gray-500">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ number_format($teamStats['total_hours'] ?? 0, 1) }} heures totales
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
                    <span>{{ $teamStats['approved_reports'] ?? 0 }} approuvés</span>
                    <span>{{ $teamStats['rejected_reports'] ?? 0 }} rejetés</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques détaillées -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <!-- Rapports soumis -->
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Soumis</p>
                    <p class="text-xl font-bold text-gray-900">{{ $reportsByStatus['soumis'] ?? 0 }}</p>
                </div>
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Approuvés -->
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Approuvés</p>
                    <p class="text-xl font-bold text-gray-900">{{ $reportsByStatus['approuvé'] ?? 0 }}</p>
                </div>
                <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Rejetés -->
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Rejetés</p>
                    <p class="text-xl font-bold text-gray-900">{{ $reportsByStatus['rejeté'] ?? 0 }}</p>
                </div>
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Brouillons -->
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Brouillons</p>
                    <p class="text-xl font-bold text-gray-900">{{ $reportsByStatus['brouillon'] ?? 0 }}</p>
                </div>
                <div class="p-2 bg-gray-100 rounded-lg">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Activités récentes et Top Performers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Activités récentes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Activités récentes
                </h2>
            </div>
            <div class="p-4 max-h-80 overflow-y-auto">
                @forelse($recentActivities as $activity)
                    <div class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-gray-50 px-2 rounded-lg transition-colors">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">{{ $activity['titre'] }}</p>
                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                <span class="font-medium text-gray-700">{{ $activity['user_name'] }}</span>
                                <span class="mx-1">•</span>
                                <span>{{ $activity['project_name'] }}</span>
                                <span class="mx-1">•</span>
                                <span>{{ $activity['duration'] }}h</span>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $activity['status'] }}
                            </span>
                            <span class="ml-2 text-xs text-gray-400">{{ $activity['created_at'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p>Aucune activité récente</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Top Performers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Top Performers du mois
                </h2>
            </div>
            <div class="p-4">
                @forelse($topPerformers as $index => $performer)
                    <div class="flex items-center justify-between py-3 border-b border-gray-50 hover:bg-gray-50 px-2 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                                {{ $index === 0 ? 'bg-yellow-100 text-yellow-600' :
                                   ($index === 1 ? 'bg-gray-200 text-gray-600' :
                                    ($index === 2 ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600')) }}">
                                <span class="font-bold text-sm">{{ $index + 1 }}</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $performer['name'] }}</p>
                                <div class="flex items-center text-xs text-gray-500">
                                    <span>{{ $performer['activities_count'] ?? 0 }} activités</span>
                                    <span class="mx-1">•</span>
                                    <span>{{ $performer['total_hours'] ?? 0 }} heures</span>
                                </div>
                            </div>
                        </div>
                        @if($index === 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                🏆 Meilleur
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p>Aucun performer à afficher</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Équipe -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Membres de l'équipe ({{ count($teamMembers) }})
            </h2>
        </div>
        <div class="p-4">
            @forelse($teamMembers as $member)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 hover:bg-gray-50 px-3 rounded-lg transition-colors">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                            {{ strtoupper(substr($member['name'], 0, 2)) }}
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $member['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $member['email'] }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                        Actif
                    </span>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <p>Aucun membre dans votre équipe</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
