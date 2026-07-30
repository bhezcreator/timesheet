<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Liste rapports/mois
        </h2>
    </x-slot>

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Liste des Rapports
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Liste des rapports mensuels
            </p>
        </div>

        <div class="mt-4 sm:mt-0 flex space-x-3">
            <button
                wire:click="printReports"
                wire:loading.attr="disabled"
                wire:target="printReports"
                class="inline-flex items-center cursor-pointer px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg wire:loading.remove wire:target="printReports" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <svg wire:loading wire:target="printReports" class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="printReports">Imprimer</span>
                <span wire:loading wire:target="printReports">Préparation...</span>
            </button>

            <button
                wire:click="$refresh"
                wire:loading.attr="disabled"
                wire:target="$refresh"
                class="inline-flex items-center cursor-pointer px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg wire:loading.remove wire:target="$refresh" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading wire:target="$refresh" class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="$refresh">Actualiser</span>
                <span wire:loading wire:target="$refresh">Actualisation...</span>
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase">Total</p>
            <p class="text-2xl font-bold text-gray-900">{{ $reports->total() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase">Soumis</p>
            <p class="text-2xl font-bold text-blue-600">{{ $totalSoumis }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase">Approuvés</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $totalApprouves }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase">Rejetés</p>
            <p class="text-2xl font-bold text-red-600">{{ $totalRejetes }}</p>
        </div>
    </div>

    <!-- Filtres - Version Moderne -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6 mb-8 backdrop-blur-sm transition-all duration-300 hover:shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5">
            <div class="flex items-center space-x-2">
                <div class="p-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Filtres avancés</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $reports->total() }} résultats
                </span>
            </div>

            <div class="flex items-center space-x-2 mt-3 sm:mt-0">
                <button wire:click="resetFilters" class="text-xs text-gray-500 hover:text-blue-600 cursor-pointer transition-colors duration-200 px-3 py-1 rounded-lg hover:bg-blue-50">
                    Réinitialiser
                </button>
                <div class="h-4 w-px bg-gray-200"></div>
                <span class="text-xs text-gray-400">
                    @if($status || $search || ($month != now()->format('m')) || ($year != now()->format('Y')))
                        Filtres actifs
                    @else
                        Tous les rapports
                    @endif
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Filtre Statut -->
            <div class="relative group">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5 flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Statut
                </label>
                <select
                    wire:model.live="status"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 focus:outline-none appearance-none cursor-pointer bg-white hover:border-gray-400"
                >
                    <option value="">Tous les statuts</option>
                    <option value="soumis">Soumis</option>
                    <option value="approuvé">Approuvé</option>
                    <option value="rejeté">Rejeté</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none top-6">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- Filtre Mois -->
            <div class="relative group">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5 flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Mois
                </label>
                <select
                    wire:model.live="month"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 focus:outline-none appearance-none cursor-pointer bg-white hover:border-gray-400"
                >
                    @php
                        $mois = [
                            '01' => 'Janvier',
                            '02' => 'Février',
                            '03' => 'Mars',
                            '04' => 'Avril',
                            '05' => 'Mai',
                            '06' => 'Juin',
                            '07' => 'Juillet',
                            '08' => 'Août',
                            '09' => 'Septembre',
                            '10' => 'Octobre',
                            '11' => 'Novembre',
                            '12' => 'Décembre',
                        ];
                    @endphp
                    @foreach($mois as $key => $moisNom)
                        <option value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>
                            {{ $moisNom }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none top-6">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- Filtre Année -->
            <div class="relative group">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5 flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Année
                </label>
                <select
                    wire:model.live="year"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 focus:outline-none appearance-none cursor-pointer bg-white hover:border-gray-400"
                >
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none top-6">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- Recherche -->
            <div class="relative group">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5 flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Recherche
                </label>
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce="search"
                        placeholder="Nom, email ou ID..."
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 focus:outline-none placeholder-gray-400 bg-white hover:border-gray-400"
                    >
                    @if($search)
                        <button wire:click="$set('search', '')"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @else
                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Filtres actifs (tags) -->
        @if($status || $search || ($month != now()->format('m')) || ($year != now()->format('Y')))
            <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap items-center gap-2">
                <span class="text-xs text-gray-500 font-medium">Filtres actifs :</span>

                @if($status)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                        Statut: {{ ucfirst($status) }}
                        <button wire:click="$set('status', '')" class="ml-1.5 text-blue-400 hover:text-blue-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                @endif

                @if($month != now()->format('m') || $year != now()->format('Y'))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                        {{ Carbon\Carbon::createFromDate($year, $month, 1)->locale('fr')->format('F Y') }}
                        <button wire:click="$set('month', '{{ now()->format('m') }}'); $set('year', '{{ now()->format('Y') }}')" class="ml-1.5 text-purple-400 hover:text-purple-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                @endif

                @if($search)
                    <span class="inline-flex items-center px-3 py-1 cursor-pointer rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                        Recherche: "{{ $search }}"
                        <button wire:click="$set('search', '')" class="ml-1.5 text-gray-400 hover:text-gray-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                @endif

                <button wire:click="resetFilters" class="text-xs text-blue-600 hover:text-blue-800 font-medium hover:underline">
                    Tout effacer
                </button>
            </div>
        @endif
    </div>

    <!-- Tableau des rapports -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activités</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date soumission</th>
                        {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th> --}}
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reports as $report)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ ($reports->currentPage() - 1) * $reports->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-semibold text-xs">
                                        {{ strtoupper(substr($report->user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $report->user->name ?? 'N/A' }} {{ $report->user->first_name ?? ' ' }}</p>
                                        <p class="text-xs text-gray-500">{{ $report->user->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ Carbon\Carbon::createFromDate($report->year, $report->month, 1)->locale('fr')->format('F Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @php
                                    $projectIds = $report->project_ids;

                                    // Convertir en tableau si ce n'est pas déjà un tableau
                                    if (is_string($projectIds)) {
                                        $projectIds = json_decode($projectIds, true);
                                    }

                                    // Si ce n'est pas un tableau, le mettre dans un tableau
                                    if (!is_array($projectIds)) {
                                        $projectIds = $projectIds ? [$projectIds] : [];
                                    }

                                    // Filtrer les valeurs null ou vides
                                    $projectIds = array_filter($projectIds);
                                @endphp

                                @if(empty($projectIds))
                                    <span class="text-gray-400">Tous</span>
                                @else
                                    {{ count($projectIds) }} projet(s)
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'brouillon' => 'bg-gray-100 text-gray-800',
                                        'soumis' => 'bg-blue-100 text-blue-800',
                                        'approuvé' => 'bg-emerald-100 text-emerald-800',
                                        'rejeté' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$report->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $report->activities->count() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $report->submitted_at ? Carbon\Carbon::parse($report->submitted_at)->format('d/m/Y H:i') : '-' }}
                            </td>
                            {{--
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <a href="#"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-lg font-medium">Aucun rapport trouvé</p>
                                    <p class="text-sm">Ajustez vos filtres pour voir plus de résultats</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <!-- Liens de pagination réactifs -->
        <div class="mt-5">
            <x-ui.pagination :paginator="$reports" />
        </div>
    </div>

    <!-- Script pour l'impression -->
    @push('scripts')
        <script>
            // Animation pour les filtres
            document.addEventListener('livewire:load', function () {
                // Ajouter une classe d'animation lors du changement de filtre
                Livewire.hook('message.processed', () => {
                    const filters = document.querySelector('.filters-container');
                    if (filters) {
                        filters.classList.add('filters-updated');
                        setTimeout(() => filters.classList.remove('filters-updated'), 500);
                    }
                });
            });
        </script>

        <style>
            /* Animation pour les filtres */
            .filters-updated {
                animation: filterPulse 0.5s ease;
            }

            @keyframes filterPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.01); }
            }

            /* Style personnalisé pour les selects */
            select {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
                background-position: right 1rem center;
                background-repeat: no-repeat;
                background-size: 1.5em 1.5em;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                padding-right: 2.5rem;
            }

            select:focus {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%233b82f6' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            }

            /* Effet de survol amélioré */
            .filter-group:hover select,
            .filter-group:hover input {
                border-color: #94a3b8;
            }

            /* Transition fluide */
            select, input {
                transition: all 0.2s ease-in-out;
            }
        </style>
    @endpush
</div>
