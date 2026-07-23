<div class="p-0 space-y-6">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rapport d'Activité
        </h2>
    </x-slot>

    <div class="flex items-center mb-4">
        <a href="{{ route('rapports.index') }}"
            wire:navigate class="font-semibold text-gray-500 inline-flex items-center gap-2 text-sm hover:text-indigo-600 transition-colors">
            <i class="las la-arrow-left text-base"></i> Retour
        </a>
    </div>

    <!-- En-tête de la page -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white border-t border-t-blue-700 p-4 rounded-2xl shadow-xs gap-4">
        <div>
            <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Espace Rapports d'Activité</span>
            <h1 class="text-2xl md:text-2xl font-black text-gray-900 mt-1">
                {{ $ID_report ? 'Modifier le Rapport' : 'Générer un Nouveau Rapport' }}
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">Périodicité configurée : <span class="font-bold text-gray-700 capitalize">{{ $reportFrequency === 'month' ? 'Mensuelle' : 'Hebdomadaire' }}</span></p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <button wire:click="save(false)" wire:loading.attr="disabled" class="flex-1 sm:flex-none cursor-pointer px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition shadow-2xs">
                <i class="las la-save mr-1.5 text-base"></i> Enregistrer le brouillon
            </button>
            <button wire:click="save(true)" wire:loading.attr="disabled" class="flex-1 sm:flex-none cursor-pointer px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-xs flex items-center justify-center">
                <i class="las la-paper-plane mr-1.5 text-base"></i> Soumettre le rapport
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- COLONNE GAUCHE : Configuration & Contenu du Rapport (Prend 2 colonnes sur grand écran) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Section 1 : Période & Périmètre -->
            <div class="bg-white p-6 rounded-2xl shadow-xs space-y-4 border-t border-t-blue-700">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2">
                    <i class="las la-sliders-h text-blue-500 text-lg"></i> 1. Période & Périmètre du Rapport
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Filtre Mois -->
                    <div class="relative w-full group" x-data="{ val: @entangle('month').live }">
                        <select x-model="val"
                                class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition duration-150">
                            <option value="">Tous les mois</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ now()->month($m)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>

                        <!-- Bouton Réinitialiser (Visible si une option est choisie) -->
                        <div x-show="val" x-cloak class="absolute inset-y-0 right-3 flex items-center">
                            <button type="button" @click="val = ''" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                                <i class="las la-times-circle text-base"></i>
                            </button>
                        </div>

                        <!-- Flèche personnalisée (Masquée si une option est choisie) -->
                        <div x-show="!val" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                            <i class="las la-angle-down text-xs"></i>
                        </div>
                    </div>

                    <!-- Filtre Année -->
                    <div class="relative w-full group" x-data="{ val: @entangle('year').live }">
                        <select x-model="val"
                                class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition duration-150">
                            <option value="">Toutes les années</option>
                            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>

                        <!-- Bouton Réinitialiser (Visible si une option est choisie) -->
                        <div x-show="val" x-cloak class="absolute inset-y-0 right-3 flex items-center">
                            <button type="button" @click="val = ''" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                                <i class="las la-times-circle text-base"></i>
                            </button>
                        </div>

                        <!-- Flèche personnalisée (Masquée si une option est choisie) -->
                        <div x-show="!val" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                            <i class="las la-angle-down text-xs"></i>
                        </div>
                    </div>

                    <!-- Filtre Regrouper les projets -->
                    <div class="relative w-full group" x-data="{ val: @entangle('selected_project_id') }">
                        <select wire:model.live="selected_project_id"
                                class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition duration-150">
                            <option value="all">Tous les projets</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>

                        <!-- Bouton Réinitialiser (Visible si un projet spécifique est choisi) -->
                        <div x-show="val && val !== 'all'" x-cloak class="absolute inset-y-0 right-3 flex items-center">
                            <button type="button" @click="val = 'all'" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                                <i class="las la-times-circle text-base"></i>
                            </button>
                        </div>

                        <!-- Icône de Dossier Line-Awesome (Masquée si une option est annulée) -->
                        <div x-show="!val || val === 'all'" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                            <i class="las la-folder-open text-base"></i>
                        </div>
                    </div>
                </div>

                <!-- Alertes Flash de Session -->
                @if(session('success'))
                    <x-ui.alert type="success" class="mb-4">
                        {{ session('success') }}
                    </x-ui.alert>
                @endif
            </div>

            <!-- Section 2 : Rédaction (Objectifs, Réalisations, Suivant) -->
            <div class="bg-white p-4 rounded-2xl shadow-xs space-y-4 border-t border-t-blue-700">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2">
                    <i class="las la-pen-alt text-blue-500 text-lg"></i> 2. Contenu Rédactionnel
                </h3>

                <div>
                    <x-ui.forms.textarea
                        name="objectives"
                        wire:model="objectives"
                        required
                        label="Les Objectifs fixés"
                        helper="Décrivez les objectifs fixés pour cette période."
                        placeholder="Décrivez les objectifs fixés pour cette période..."
                    />
                    @error('objectives') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-ui.forms.textarea
                        name="achievements"
                        wire:model="achievements"
                        required
                        label="Les Réalisations effectuées"
                        helper="Qu'avez-vous accompli avec succès durant cette période ?"
                        placeholder="Qu'avez-vous accompli avec succès durant cette période ?"
                    />
                    @error('achievements') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-ui.forms.textarea
                        name="next_actions"
                        wire:model="next_actions"
                        required
                        label="Les Prochaines actions prévues"
                        helper="Quelles sont les prochaines étapes ou tâches prioritaires ?"
                        placeholder="Quelles sont les prochaines étapes ou tâches prioritaires ?"
                    />
                    @error('next_actions') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- COLONNE DROITE : Récapitulatif des Activités & Heures (Prend 1 colonne) -->
        <div class="space-y-4">
            <!-- Widgets d'Heures & Overtime -->
            <div class="bg-gradient-to-br from-blue-900 to-slate-900 text-white p-4 rounded-2xl shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-blue-200 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="las la-chart-pie text-base"></i> Récapitulatif du temps
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/10 p-3 rounded-xl backdrop-blur-xs">
                        <span class="text-[10px] text-blue-200 block uppercase font-medium">Heures Déclarante(s)</span>
                        <span class="text-2xl font-black block mt-0.5">{{ round($totalHours) }}h</span>
                    </div>

                    @if($calculateOvertime)
                        <div class="bg-white/10 p-3 rounded-xl backdrop-blur-xs relative overflow-hidden">
                            <span class="text-[10px] text-amber-300 block uppercase font-medium">Heures Sup.</span>
                            <span class="text-2xl font-black text-amber-400 block mt-0.5">{{ round($overtimeHours) }}h</span>
                            <div class="absolute -right-2 -bottom-2 opacity-10">
                                <i class="las la-clock text-5xl"></i>
                            </div>
                        </div>
                    @endif
                </div>

                @if($calculateOvertime)
                    <div class="text-[11px] text-blue-200/80 pt-1 border-t border-white/10 flex justify-between">
                        <span>Seuil standard mensuel :</span>
                        <span class="font-bold text-white">{{ round($standardHoursPerMonth) }} heures</span>
                    </div>
                @endif
            </div>

            <!-- Liste dynamique des activités capturées -->
            <div class="bg-white p-4 rounded-2xl shadow-xs space-y-4 max-h-[515px] flex flex-col border-t border-t-blue-700">
                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-1.5">
                        <i class="las la-history text-blue-500 text-base"></i> Activités indexées ({{ $activitiesList->count() }})
                    </h3>
                </div>

                <div class="space-y-2 flex-1 overflow-y-auto pr-1 custom-scrollbar">
                    @forelse($activitiesList as $activity)
                        <div class="p-3 bg-gray-50 hover:bg-gray-100/70 border border-gray-100 rounded-xl transition flex items-start justify-between gap-2"
                            style="border-left: 3px solid {{ $activity->activityType->color ?? '#cbd5e1' }}">
                            <div class="space-y-0.5 min-w-0">
                                <h4 class="text-xs font-bold text-gray-800 truncate" title="{{ $activity->titre }}">
                                    {{ $activity->titre }}
                                </h4>
                                <span class="text-[10px] text-gray-400 block font-medium">
                                    {{ $activity->project->name ?? 'Sans projet' }}
                                </span>
                                <span class="text-[10px] text-gray-500 inline-flex items-center gap-1">
                                    <i class="las la-calendar"></i> {{ $activity->activity_date->format('d/m/Y') }}
                                </span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 bg-white border border-gray-200 rounded-md text-[10px] font-bold text-gray-700 shadow-3xs">
                                    {{ round($activity->duration) }}h
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 space-y-2">
                            <i class="las la-folder-open text-4xl block text-gray-300"></i>
                            <p class="text-xs">Aucune activité enregistrée pour ce mois ou projet sélectionné.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
