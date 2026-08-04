<div>
    <!-- En-tête avec statistiques -->
    <div class="bg-white rounded-lg shadow-sm mb-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="las la-sliders-h text-blue-600 mr-2"></i>
                    Journal d'activité
                </h1>
                <p class="text-sm text-gray-500 mt-1">Historique complet de toutes les actions système</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-500">Total:</span>
                    <span class="font-semibold text-gray-700">{{ $eventsCount['total'] }}</span>
                </div>
                <span class="text-gray-300">|</span>
                <div class="flex items-center gap-3 text-sm">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        <span class="text-gray-600">{{ $eventsCount['created'] }} créés</span>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                        <span class="text-gray-600">{{ $eventsCount['updated'] }} modifiés</span>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        <span class="text-gray-600">{{ $eventsCount['deleted'] }} supprimés</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-sm mb-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <!-- Recherche -->
            <div class="lg:col-span-2">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="las la-search"></i>
                    </span>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher..."
                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    >
                </div>
            </div>

            <!-- Type de log -->
            <div>
                <select
                    wire:model.live="logName"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                    <option value="">Tous les types</option>
                    @foreach($logNames as $name)
                        <option value="{{ $name }}">{{ ucfirst(str_replace('_', ' ', $name)) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Événement -->
            <div>
                <select
                    wire:model.live="event"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                    <option value="">Tous les événements</option>
                    @foreach($events as $event)
                        <option value="{{ $event }}">{{ ucfirst($event) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date de début -->
            <div>
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
            </div>

            <!-- Date de fin -->
            <div>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
            </div>
        </div>

        <!-- Actions des filtres -->
        <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-center gap-3">
                <label class="text-sm text-gray-600">Lignes par page:</label>
                <select
                    wire:model.live="perPage"
                    class="px-2 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <button
                wire:click="clearFilters"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
            >
                <i class="las la-undo mr-2"></i>
                Réinitialiser
            </button>
        </div>
    </div>

    <!-- Tableau des logs -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            wire:click="sortBy('created_at')"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors"
                        >
                            <div class="flex items-center gap-1">
                                Date/Heure
                                @if($sortField === 'created_at')
                                    <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="las la-sort text-gray-300"></i>
                                @endif
                            </div>
                        </th>
                        <th
                            wire:click="sortBy('log_name')"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors"
                        >
                            <div class="flex items-center gap-1">
                                Type
                                @if($sortField === 'log_name')
                                    <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="las la-sort text-gray-300"></i>
                                @endif
                            </div>
                        </th>
                        <th
                            wire:click="sortBy('event')"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors"
                        >
                            <div class="flex items-center gap-1">
                                Événement
                                @if($sortField === 'event')
                                    <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="las la-sort text-gray-300"></i>
                                @endif
                            </div>
                        </th>
                        <th
                            wire:click="sortBy('subject_type')"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors"
                        >
                            <div class="flex items-center gap-1">
                                Modèle
                                @if($sortField === 'subject_type')
                                    <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="las la-sort text-gray-300"></i>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Utilisateur
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $log->log_name ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $this->getEventBadgeColor($log->event) }}-100 text-{{ $this->getEventBadgeColor($log->event) }}-800">
                                    <i class="las {{ $this->getEventIcon($log->event) }}"></i>
                                    {{ ucfirst($log->event ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="las {{ $this->getModelIcon($this->getSubjectTypeName($log->subject_type)) }} text-{{ $this->getModelColor($this->getSubjectTypeName($log->subject_type)) }}"></i>
                                    <span class="text-sm text-gray-900">
                                        {{ $this->getSubjectTypeName($log->subject_type) }}
                                    </span>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900 max-w-xs truncate">
                                    {{ $log->description }}
                                </div>
                                @if($log->subject)
                                    <div class="text-xs text-gray-500">
                                        ID: #{{ $log->subject_id }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($log->causer)
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-medium">
                                            {{ $log->causer->initial ?? substr($log->causer->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $log->causer->full_name ?? $log->causer->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $log->causer->email ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">Système</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <x-ui.tooltip text="Voir les détails">
                                    <button
                                        wire:click="viewDetails({{ $log->id }})"
                                        class="text-blue-600 hover:text-blue-800 transition-colors cursor-pointer">
                                        <i class="las la-eye"></i>
                                    </button>
                                </x-ui.tooltip>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                <i class="las la-inbox text-4xl mb-3 block text-gray-300"></i>
                                <p class="text-lg font-medium">Aucun log trouvé</p>
                                <p class="text-sm">Aucune activité correspondant à vos critères n'a été enregistrée.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-5">
            <x-ui.pagination :paginator="$logs" />
        </div>
    </div>

    <!-- Modal Détails -->
    @if($showDetailsModal && $selectedLog)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ open: true }" x-show="open">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md bg-opacity-50 transition-opacity" wire:click="closeDetailsModal"></div>

                <!-- Modal -->
                <div class="relative bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                    <!-- En-tête -->
                    <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="las la-info-circle text-blue-600 mr-2"></i>
                            Détails du log
                        </h3>
                        <button wire:click="closeDetailsModal" class="text-gray-400 hover:text-gray-600 transition-colors  cursor-pointer">
                            <i class="las la-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Corps -->
                    <div class="px-6 py-4 space-y-4">
                        <!-- Informations principales -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">ID</label>
                                <p class="mt-1 text-sm text-gray-900">#{{ $selectedLog->id }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Heure</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedLog->created_at->format('d/m/Y H:i:s') }}</p>
                                <p class="text-xs text-gray-500">{{ $selectedLog->created_at->diffForHumans() }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Type de log</label>
                                <p class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $selectedLog->log_name ?? 'N/A')) }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Événement</label>
                                <p class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $this->getEventBadgeColor($selectedLog->event) }}-100 text-{{ $this->getEventBadgeColor($selectedLog->event) }}-800">
                                        <i class="las {{ $this->getEventIcon($selectedLog->event) }}"></i>
                                        {{ ucfirst($selectedLog->event ?? 'N/A') }}
                                    </span>
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Description</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedLog->description }}</p>
                            </div>
                        </div>

                        <!-- Utilisateur -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">
                                <i class="las la-user text-blue-600 mr-2"></i>
                                Utilisateur
                            </h4>
                            @if($selectedLog->causer)
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                                        {{ $selectedLog->causer->initial ?? substr($selectedLog->causer->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $selectedLog->causer->full_name ?? $selectedLog->causer->name ?? 'N/A' }}
                                        </p>
                                        <p class="text-sm text-gray-500">{{ $selectedLog->causer->email ?? '' }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-gray-400">Système</p>
                            @endif
                        </div>

                        <!-- Modèle -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">
                                <i class="las la-database text-purple-600 mr-2"></i>
                                Modèle
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Type</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="las {{ $this->getModelIcon($this->getSubjectTypeName($selectedLog->subject_type)) }} text-{{ $this->getModelColor($this->getSubjectTypeName($selectedLog->subject_type)) }}"></i>
                                            {{ $this->getSubjectTypeName($selectedLog->subject_type) }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">ID</label>
                                    <p class="mt-1 text-sm text-gray-900">#{{ $selectedLog->subject_id ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Propriétés (changements) -->
                        @if($selectedLog->properties && count($selectedLog->properties) > 0)
                            <div class="border-t border-gray-200 pt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">
                                    <i class="las la-code text-green-600 mr-2"></i>
                                    Données
                                </h4>
                                <div class="bg-gray-50 rounded-lg p-4 overflow-x-auto">
                                    <pre class="text-xs text-gray-800 whitespace-pre-wrap break-all">{{ json_encode($selectedLog->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Pied -->
                    <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-3 flex justify-end">
                        <button
                            wire:click="closeDetailsModal"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition-colors cursor-pointer"
                        >
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Styles supplémentaires pour les couleurs personnalisées -->
<style>
    /* Couleurs personnalisées */
    .bg-purple-100 { background-color: #f3e8ff; }
    .text-purple-800 { color: #6b21a8; }
    .bg-orange-100 { background-color: #fff7ed; }
    .text-orange-800 { color: #9a3412; }
    .bg-teal-100 { background-color: #f0fdfa; }
    .text-teal-800 { color: #115e59; }
    .text-purple { color: #7c3aed; }
    .text-orange { color: #ea580c; }
    .text-teal { color: #0d9488; }

    /* Assure que les icônes Line Awesome s'affichent correctement */
    .las, .lar, .lab {
        display: inline-block;
        font-size: 1.2em;
        line-height: 1;
    }

    /* Styles pour les badges de tri */
    .cursor-pointer {
        cursor: pointer;
        user-select: none;
    }

    .transition-colors {
        transition: color 0.2s ease;
    }

    /* Animation pour le modal */
    [x-show="open"] {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>
</div>
