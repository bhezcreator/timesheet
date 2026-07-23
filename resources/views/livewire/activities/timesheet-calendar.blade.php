<div class="p-0 space-y-6">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Feuille de temps
        </h2>
    </x-slot>
    <!-- Barre de Navigation Moderne -->
    <div class="flex flex-col sm:flex-row justify-between items-center bg-white p-4 shadow-sm gap-4 border-t border-t-blue-700">
        <!-- Vues (Année, Mois, Semaine) -->
        <div class="inline-flex p-1 bg-gray-100 rounded-lg w-full sm:w-auto">
            <button wire:click="changeView('year')" class="flex-1 sm:flex-initial cursor-pointer px-4 py-2 text-sm font-medium rounded-md transition {{ $viewMode === 'year' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                Année
            </button>
            <button wire:click="changeView('month')" class="flex-1 sm:flex-initial cursor-pointer px-4 py-2 text-sm font-medium rounded-md transition {{ $viewMode === 'month' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                Mois
            </button>
            <button wire:click="changeView('week')" class="flex-1 sm:flex-initial cursor-pointer px-4 py-2 text-sm font-medium rounded-md transition {{ $viewMode === 'week' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                Semaine
            </button>
        </div>

        <!-- Titre Fléché de Période -->
        <div class="flex items-center gap-2">
            <button wire:click="previous" class="p-2 text-gray-600 hover:bg-gray-100 cursor-pointer rounded-lg transition">
                <i class="las la-chevron-left font-bold text-lg"></i>
            </button>
            <h2 class="text-lg font-bold text-gray-800 capitalize min-w-[150px] text-center">
                {{ $navigationTitle }}
            </h2>
            <button wire:click="next" class="p-2 text-gray-600 hover:bg-gray-100 cursor-pointer rounded-lg transition">
                <i class="las la-chevron-right font-bold text-lg"></i>
            </button>
        </div>

        <!-- Bouton Aujourd'hui -->
        <button wire:click="today" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm flex items-center justify-center gap-2">
            <i class="las la-calendar-day text-lg"></i> Aujourd'hui
        </button>
    </div>

    <!-- Conteneur Calendrier Principal -->
    <div class="bg-white shadow-sm overflow-hidden">

        <!-- MODE MOIS & SEMAINE -->
        @if($viewMode === 'month' || $viewMode === 'week')
            <div class="bg-white grid grid-cols-1 md:grid-cols-7 border-b border-t border-t-blue-700 border-gray-200 hidden md:grid">
                @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $dayName)
                    <div class="py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $dayName }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-7 auto-rows-fr divide-y md:divide-y-0 md:gap-px bg-gray-200">
                @foreach($calendarData as $day)
                    @php
                        $dayStr = $day->format('Y-m-d');
                        $dayActivities = $activities->get($dayStr, []);
                        $isCurrentMonth = $day->month == \Carbon\Carbon::parse($currentDate)->month;
                        $isToday = $day->isToday();
                    @endphp

                    <div class="bg-white p-3 min-h-[120px] transition space-y-2 flex flex-col justify-between {{ $viewMode === 'month' && !$isCurrentMonth ? 'bg-gray-50 opacity-40' : '' }} {{ $isToday ? 'ring-2 ring-blue-500 ring-inset bg-blue-50/20' : '' }}">
                        <!-- En-tête du Jour -->
                        <div class="flex justify-between items-center">
                            <span class="md:hidden text-xs font-bold text-gray-400 uppercase">{{ $day->translatedFormat('l') }}</span>
                            <span class="text-sm font-semibold rounded-full p-1 w-7 h-7 flex items-center justify-center {{ $isToday ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                                {{ $day->day }}
                            </span>
                        </div>

                        <!-- Liste des Activités du jour -->
                        <div class="space-y-1.5 flex-1 overflow-y-auto max-h-[160px] custom-scrollbar">
                            @foreach($dayActivities as $activity)
                                <div class="group relative bg-gray-50 hover:bg-white border border-gray-200 hover:border-gray-300 rounded-md p-2 text-xs transition shadow-2xs flex flex-col justify-between"
                                     style="border-left: 4px solid {{ $activity->activityType->color ?? '#cbd5e1' }}">

                                    <div class="font-semibold text-gray-800 truncate mb-1 pr-10" title="{{ $activity->titre }}">
                                        {{ $activity->titre }}
                                    </div>

                                    <!-- Heures & Durée -->
                                    <div class="text-[10px] text-gray-500 flex flex-wrap items-center gap-1.5">
                                        <span class="flex items-center gap-0.5">
                                            <i class="las la-clock text-xs text-gray-400"></i>
                                            {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                                        </span>
                                        <span class="bg-gray-200 px-1 rounded text-[9px] font-bold text-gray-600">
                                            {{ $activity->duration }}h
                                        </span>
                                    </div>

                                    <!-- Actions d'activité -->
                                    <div class="absolute top-1.5 right-1.5 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity bg-inherit pl-1">
                                        <button wire:click="showDetails({{ $activity->id }})" class="text-blue-600 hover:text-blue-800 p-0.5 rounded cursor-pointer" title="Détails">
                                            <i class="las la-eye text-base"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $activity->id }}, '{{ addslashes($activity->titre) }}')" class="cursor-pointer text-red-500 hover:text-red-700 p-0.5 rounded" title="Supprimer">
                                            <i class="las la-trash text-base"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        <!-- MODE ANNÉE -->
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 p-4 gap-4 bg-gray-50">
                @foreach($calendarData as $monthDate)
                    @php
                        $monthStr = $monthDate->format('m');
                        $monthActivitiesCount = collect($activities)->flatten()->filter(fn($act) => $act->activity_date->format('m') === $monthStr)->count();
                    @endphp
                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs hover:shadow-xs transition flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-gray-800 capitalize text-base">{{ $monthDate->translatedFormat('F') }}</h3>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $monthActivitiesCount }} {{ $monthActivitiesCount > 1 ? 'activités enregistrées' : 'activité enregistrée' }}
                            </p>
                        </div>
                        <button wire:click="$set('currentDate', '{{ $monthDate->format('Y-m-d') }}'); changeView('month');" class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition">
                            <i class="las la-arrow-right"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modale A : Détails de l'activité (la-eye) -->
    <x-ui.modal-one id="details-activity-modal" title="Détails de l'activité" size="xl">
        @if($selectedActivity)
            <div class="space-y-4">
                <div class="bg-blue-50 mb-4 border-l-4 border-blue-500 p-3 rounded-lg">
                    <h4 class="text-sm font-bold text-blue-800">{{ $selectedActivity->titre }}</h4>
                </div>

                <div class="p-3 rounded-lg flex items-center gap-3 border-l-4" style="border-color: {{ $selectedActivity->activityType->color ?? '#cbd5e1' }}; bg-color: {{ ($selectedActivity->activityType->color ?? '#cbd5e1').'10' }}">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Type d'activité</span>
                        <h4 class="text-sm font-bold text-gray-800">{{ $selectedActivity->activityType->name }}</h4>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-gray-400 block">Projet</span>
                        <span class="font-semibold text-gray-700">{{ $selectedActivity->project->name ?? 'N/A' }}</span>
                    </div>

                    @if ($selectedActivity->subProject)
                        <div>
                            <span class="text-xs text-gray-400 block">Sous-projet</span>
                            <span class="font-semibold text-gray-700">{{ $selectedActivity->subProject->name ?? 'N/A' }}</span>
                        </div>
                    @endif

                    <div>
                        <span class="text-xs text-gray-400 block">Date</span>
                        <span class="font-semibold text-gray-700">{{ $selectedActivity->activity_date->translatedFormat('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Durée Totale</span>
                        <span class="font-bold text-blue-600 px-2 py-0.5 bg-blue-50 rounded">
                            {{ $selectedActivity->duration }} heures
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-gray-400 block">Heure début</span>
                        <span class="font-semibold text-gray-700">
                            {{ \Carbon\Carbon::parse($selectedActivity->start_time)->format('H:i') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Heure fin</span>
                        <span class="font-semibold text-gray-700">
                            {{ \Carbon\Carbon::parse($selectedActivity->end_time)->format('H:i') }}
                        </span>
                    </div>
                </div>
            </div>

            @if($selectedActivity->description)
                <div class="pt-2 border-t border-gray-100">
                    <span class="text-xs text-gray-400 block mb-1">Description</span>
                    <p class="text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg whitespace-pre-line leading-relaxed">
                        {{ $selectedActivity->description }}
                    </p>
                </div>
            @endif
        @endif

        <x-slot:footer>
            <div class="flex justify-end w-full">
                <x-ui.button variant="danger" data-close-modal>Fermer</x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal-one>
    <!-- Modale B : Confirmation de suppression de la ligne d'activité -->
    <x-ui.modal-one id="delete-activity-modal" title="Confirmation de suppression" size="sm">
        <div class="text-center py-2">
            <i class="las la-exclamation-triangle text-red-500 text-5xl block mb-3 animate-pulse"></i>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Supprimer l'activité ?</h3>
            <p class="text-xs text-gray-500 px-2 leading-relaxed">
                Voulez-vous supprimer définitivement la ligne
                <br>
                <span class="font-bold text-gray-900">"{{ $deleteName }}"</span> ?
                <br><br>
                Cette opération est irréversible et la retirera de vos statistiques mensuelles.
            </p>
        </div>
        <x-slot:footer>
            <div class="flex justify-center w-full gap-3">
                <x-ui.button variant="outline" data-close-modal>Annuler</x-ui.button>
                <x-ui.button variant="danger" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
                    <span wire:loading.remove wire:target="delete" class="flex items-center gap-1">
                        <i class="las la-trash"></i> Confirmer la suppression
                    </span>

                    <span wire:loading wire:target="delete" class="flex items-center gap-2">
                        <i class="las la-spinner animate-spin"></i> Suppression...
                    </span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal-one>
</div>
