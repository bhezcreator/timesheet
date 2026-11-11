<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Détail des activités
        </h2>
    </x-slot>

       {{-- Bouton Retour --}}
    <div class="flex items-center justify-between mb-3 p-0">
        <a href="{{ route('dashboard') }}"
            wire:navigate class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors font-medium">
            <i class="las la-arrow-left text-base"></i> <strong>Retour</strong>
        </a>
    </div>

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                <i class="las la-user-circle text-indigo-500 text-3xl align-middle mr-2"></i>
                Activités de <span class="text-indigo-600">{{ $user->name }}</span>
            </h2>
            <p class="text-sm text-gray-400 mt-1 flex items-center gap-2">
                <i class="las la-calendar-alt text-gray-300"></i>
                {{ now()->monthName }} {{ now()->year }}
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                {{ $stats['total'] ?? 0 }} activité(s)
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full border border-indigo-100">
                <i class="las la-calendar-check text-indigo-500"></i>
                {{ now()->format('d/m/Y') }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 text-gray-600 text-xs font-medium rounded-full border border-gray-100">
                <i class="las la-clock text-gray-400"></i>
                Mois en cours
            </span>
        </div>
    </div>

    <div class="w-full mt-0">

       <!-- Cartes de statistiques modernes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
            <!-- Total -->
            <div class="group relative bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100/80 overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-indigo-500 to-indigo-600"></div>
                <div class="p-4 pl-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <i class="las la-tasks text-indigo-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 inline-block"></span>
                        Activités du mois
                    </p>
                </div>
            </div>

            <!-- Brouillon -->
            <div class="group relative bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100/80 overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-gray-400 to-gray-500"></div>
                <div class="p-4 pl-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Brouillon</p>
                            <p class="text-2xl font-bold text-gray-600 mt-1">{{ $stats['brouillon'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <i class="las la-pencil-alt text-gray-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span>
                        En attente
                    </p>
                </div>
            </div>

            <!-- Soumis -->
            <div class="group relative bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100/80 overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-amber-400 to-amber-500"></div>
                <div class="p-4 pl-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Soumis</p>
                            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['soumis'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <i class="las la-paper-plane text-amber-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>
                        En validation
                    </p>
                </div>
            </div>

            <!-- Approuvé -->
            <div class="group relative bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100/80 overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-emerald-400 to-emerald-500"></div>
                <div class="p-4 pl-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Approuvé</p>
                            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['approuvé'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <i class="las la-check-circle text-emerald-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                        Validé
                    </p>
                </div>
            </div>

            <!-- Rejeté -->
            <div class="group relative bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100/80 overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-rose-400 to-rose-500"></div>
                <div class="p-4 pl-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Rejeté</p>
                            <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['rejeté'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-rose-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <i class="las la-times-circle text-rose-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 inline-block"></span>
                        À corriger
                    </p>
                </div>
            </div>

            <!-- Heures -->
            <div class="group relative bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100/80 overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-violet-400 to-violet-500"></div>
                <div class="p-4 pl-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Heures</p>
                            <p class="text-2xl font-bold text-violet-600 mt-1">{{ number_format($stats['total_heures'], 1) }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-violet-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <i class="las la-clock text-violet-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-400 inline-block"></span>
                        Total
                    </p>
                </div>
            </div>
        </div>

        <!-- Alertes de session -->
        @if(session('success'))
            <x-ui.alert type="success" class="mb-4 mt-8">
                {{ session('success') }}
            </x-ui.alert> <br />
        @endif

        @error('permission')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert> <br />
        @enderror

        <!-- Liste des activités -->
        @if($activities->count() >= 0)
            <!-- Filtres et recherche -->
            <div class="flex flex-wrap items-center gap-3 mb-4 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                <!-- Filtre par statut -->
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Statut:</span>
                    <select wire:model.live="statusFilter" 
                            class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">Tous</option>
                        <option value="brouillon">Brouillon</option>
                        <option value="soumis">Soumis</option>
                        <option value="approuvé">Approuvé</option>
                        <option value="rejeté">Rejeté</option>
                    </select>
                </div>

                <!-- Barre de recherche -->
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <i class="las la-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" 
                               wire:model.live.debounce="search" 
                               placeholder="Rechercher une activité..."
                               class="w-full pl-9 pr-3 py-1.5 text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Nombre par page -->
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Afficher:</span>
                    <select wire:model.live="perPage" 
                            class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <!-- Bouton réinitialiser -->
                <button wire:click="resetFilters" 
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors ml-auto">
                    <i class="las la-undo"></i> Réinitialiser
                </button>
            </div>
        @endif

        @if($activities->count() > 0 || !empty($search))
            <!-- Tableau des activités -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th wire:click="sortBy('titre')" 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700">
                                    <div class="flex items-center gap-1">
                                        Activité
                                        @if($sortBy === 'titre')
                                            <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('project_id')" 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700">
                                    <div class="flex items-center gap-1">
                                        Projet
                                        @if($sortBy === 'project_id')
                                            <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('status')" 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700">
                                    <div class="flex items-center gap-1">
                                        Statut
                                        @if($sortBy === 'status')
                                            <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('activity_date')" 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700">
                                    <div class="flex items-center gap-1">
                                        Date
                                        @if($sortBy === 'activity_date')
                                            <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('duration')" 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700">
                                    <div class="flex items-center gap-1">
                                        Durée
                                        @if($sortBy === 'duration')
                                            <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($activities as $activity)
                                <tr class="hover:bg-gray-50 transition-colors" wire:key="activity-{{ $activity->id }}">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $activity->titre ?? 'Sans titre' }}
                                            </div>
                                            @if($activity->description)
                                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                    {{ Str::limit($activity->description, 80) }}
                                                </p>
                                            @endif
                                            @if($activity->subProject)
                                                <span class="text-xs text-gray-400 mt-1">
                                                    <i class="las la-folder"></i> {{ $activity->subProject->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-700">
                                            {{ $activity->project->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'brouillon' => 'bg-gray-100 text-gray-800',
                                                'soumis' => 'bg-yellow-100 text-yellow-800',
                                                'approuvé' => 'bg-green-100 text-green-800',
                                                'rejeté' => 'bg-red-100 text-red-800',
                                            ];
                                            $statusIcons = [
                                                'brouillon' => 'la-pencil-alt',
                                                'soumis' => 'la-paper-plane',
                                                'approuvé' => 'la-check-circle',
                                                'rejeté' => 'la-times-circle',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$activity->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            <i class="las {{ $statusIcons[$activity->status] ?? 'la-circle' }}"></i>
                                            {{ ucfirst($activity->status ?? 'Non défini') }}
                                        </span>
                                        @if($activity->status === 'rejeté' && $activity->rejection_reason)
                                            <div class="text-xs text-red-500 mt-1" title="{{ $activity->rejection_reason }}">
                                                <i class="las la-info-circle"></i> {{ Str::limit($activity->rejection_reason, 25) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-700">
                                            @if($activity->activity_date)
                                                <span class="text-xs">{{ $activity->activity_date->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-xs text-gray-400">Non définie</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-700">
                                            @if($activity->duration)
                                                {{ number_format($activity->duration, 2) }}h
                                            @elseif($activity->start_time && $activity->end_time)
                                                @php
                                                    $start = \Carbon\Carbon::parse($activity->start_time);
                                                    $end = \Carbon\Carbon::parse($activity->end_time);
                                                    $diff = $start->diffInHours($end) . 'h' . $start->diffInMinutes($end) % 60 . 'min';
                                                @endphp
                                                {{ $diff }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <x-ui.button 
                                            variant="outline" 
                                            wire:click="openStatusModal({{ $activity->id }})" 
                                            title="Modifier le statut"
                                            class="text-xs">
                                            <i class="las la-exchange-alt"></i>
                                        </x-ui.button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $activities->links() }}
                </div>
            </div>
        @else
            <!-- Message vide -->
            <x-ui.empty-state 
                title="Aucune activité" 
                description="{{ $user->name }} n'a pas encore d'activités pour ce mois." 
                icon="las la-tasks">
            </x-ui.empty-state>
        @endif
    </div>

    <!-- MODALE : Modification du statut -->
    <x-ui.modal
        :show="$showStatusModal"
        id="status-modal"
        title="Modifier le statut"
        size="lg"
        subtitle="Changer le statut de l'activité sélectionnée">
        
        <div class="space-y-4">
            <!-- Statut actuel -->
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs font-medium text-gray-500">Statut actuel</p>
                @php
                    $activity = $selectedActivityId ? App\Models\Activity::find($selectedActivityId) : null;
                @endphp
                @if($activity)
                    <span class="inline-flex items-center gap-1 px-2 py-1 text-sm font-medium rounded-full {{ $statusColors[$activity->status] ?? 'bg-gray-100 text-gray-800' }}">
                        <i class="las {{ $statusIcons[$activity->status] ?? 'la-circle' }}"></i>
                        {{ ucfirst($activity->status ?? 'Non défini') }}
                    </span>
                @endif
            </div>

            <!-- Nouveau statut -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau statut</label>
                <div class="flex flex-wrap gap-2">
                    <label class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $newStatus === 'brouillon' 
                            ? 'border-gray-600 bg-gray-100' 
                            : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50' }}">
                        <input type="radio" 
                            value="brouillon" 
                            wire:model.live="newStatus"
                            class="w-4 h-4 text-gray-600 border-gray-300 focus:ring-gray-500">
                        <span class="text-sm font-medium text-gray-700">Brouillon</span>
                    </label>

                    <label class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $newStatus === 'soumis' 
                            ? 'border-amber-500 bg-amber-50' 
                            : 'border-gray-300 hover:border-amber-400 hover:bg-amber-50' }}">
                        <input type="radio" 
                            value="soumis" 
                            wire:model.live="newStatus"
                            class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500">
                        <span class="text-sm font-medium text-amber-700">Soumis</span>
                    </label>

                    <label class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $newStatus === 'approuvé' 
                            ? 'border-emerald-500 bg-emerald-50' 
                            : 'border-gray-300 hover:border-emerald-400 hover:bg-emerald-50' }}">
                        <input type="radio" 
                            value="approuvé" 
                            wire:model.live="newStatus"
                            class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                        <span class="text-sm font-medium text-emerald-700">Approuvé</span>
                    </label>

                    <label class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all duration-200
                        {{ $newStatus === 'rejeté' 
                            ? 'border-rose-500 bg-rose-50' 
                            : 'border-gray-300 hover:border-rose-400 hover:bg-rose-50' }}">
                        <input type="radio" 
                            value="rejeté" 
                            wire:model.live="newStatus"
                            class="w-4 h-4 text-rose-600 border-gray-300 focus:ring-rose-500">
                        <span class="text-sm font-medium text-rose-700">Rejeté</span>
                    </label>
                </div>
                <x-ui.forms.error name="newStatus" />
            </div>

            <!-- Raison du rejet (affiché uniquement si le statut est "rejeté") -->
            @if($newStatus === 'rejeté')
                <div>
                    <x-ui.forms.textarea
                        label="Raison du rejet"
                        name="rejectionReason"
                        wire:model="rejectionReason"
                        placeholder="Expliquez la raison du rejet..."
                        rows="3"
                    />
                </div>
            @endif

            <!-- Informations supplémentaires -->
            @if($activity)
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-400">
                        <i class="las la-info-circle"></i>
                        Activité : <span class="font-medium text-gray-600">{{ $activity->titre ?? 'Sans titre' }}</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        <i class="las la-calendar"></i>
                        Date : {{ $activity->activity_date ? $activity->activity_date->format('d/m/Y') : 'Non définie' }}
                    </p>
                </div>
            @endif
        </div>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <x-ui.button
                    variant="outline"
                    wire:click="closeStatusModal"
                    wire:loading.attr="disabled"
                    wire:target="closeStatusModal"
                >
                    <span wire:loading.remove wire:target="closeStatusModal">Annuler</span>
                    <span wire:loading wire:target="closeStatusModal">
                        <i class="las la-spinner la-spin mr-1"></i>
                    </span>
                </x-ui.button>

                <x-ui.button
                    wire:click="updateStatus"
                    wire:loading.attr="disabled"
                    wire:target="updateStatus"
                >
                    <span wire:loading.remove wire:target="updateStatus">
                        <i class="las la-save mr-1"></i> Mettre à jour
                    </span>
                    <span wire:loading wire:target="updateStatus">
                        <i class="las la-spinner la-spin mr-1"></i> Traitement...
                    </span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal>
</div>