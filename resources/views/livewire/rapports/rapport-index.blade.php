<div class="p-0 space-y-6">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rapports
        </h2>
    </x-slot>

    <!-- En-tête de la page -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-4 border-t border-t-blue-700 rounded-2xl shadow-xs gap-4">
        <div>
            <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Historique</span>
            <h1 class="text-xl md:text-2xl font-black text-gray-900 mt-1">Mes Rapports d'Activités</h1>
            <p class="text-xs text-gray-500 mt-0.5">Consultez, modifiez ou suivez l'état de validation de vos rapports.</p>
        </div>

        <!-- Lien de création propre qui évite le double slash grâce à la route nommée sans attribut -->
        <a href="{{ route('rapports.create') }}" class="w-full sm:w-auto px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-xs flex items-center justify-center gap-2">
            <i class="las la-plus text-base"></i> Nouveau rapport
        </a>
    </div>

    <!-- Message d'erreur exclusif sur droit ou blocage structurel -->
    @error('permission')
        <x-ui.alert type="error" class="mb-6">{{ $message }}</x-ui.alert> <br>
    @enderror
    <!-- Alertes de session -->
    @if(session('message'))
        <x-ui.alert type="success" class="mb-4">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <!-- Barre de Filtres Intégrée -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 border-t border-t-blue-700 rounded-2xl shadow-xs">
        <!-- Filtre Mois -->
        <div class="relative w-full group" x-data="{ val: @entangle('month').live }">
            <select x-model="val"
                    class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition duration-150">
                <option value="">Tous les mois</option>
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}">{{ now()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>

            <!-- Bouton Reset (X) -->
            <div x-show="val" x-cloak class="absolute inset-y-0 right-3 flex items-center z-10">
                <button type="button" @click="val = ''" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                    <i class="las la-times-circle text-base"></i>
                </button>
            </div>

            <!-- Flèche Down -->
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

            <!-- Bouton Reset (X) -->
            <div x-show="val" x-cloak class="absolute inset-y-0 right-3 flex items-center z-10">
                <button type="button" @click="val = ''" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                    <i class="las la-times-circle text-base"></i>
                </button>
            </div>

            <!-- Flèche Down -->
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

            <div x-show="val && val !== 'all'" x-cloak class="absolute inset-y-0 right-3 flex items-center">
                <button type="button" @click="val = 'all'" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                    <i class="las la-times-circle text-base"></i>
                </button>
            </div>

            <div x-show="!val || val === 'all'" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                <i class="las la-folder-open text-base"></i>
            </div>
        </div>
    </div>

    <!-- Grille des rapports (Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($reports as $rapport)
            @php
                // Configuration visuelle dynamique selon le statut
                $statusConfig = match($rapport->status) {
                    'soumis' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'En attente', 'icon' => 'la-clock'],
                    'approuvé' => ['bg' => 'bg-green-50 text-green-700 border-green-200', 'label' => 'Approuvé', 'icon' => 'la-check-circle'],
                    'rejeté' => ['bg' => 'bg-red-50 text-red-700 border-red-200', 'label' => 'Rejeté', 'icon' => 'la-exclamation-circle'],
                    default => ['bg' => 'bg-gray-100 text-gray-700 border-gray-300', 'label' => 'Brouillon', 'icon' => 'la-history'],
                };
            @endphp

            <div class="bg-white rounded-2xl py-4 border border-gray-100 border-t border-t-blue-700 border-b border-b-blue-700 shadow-2xs hover:shadow-xs transition duration-200 flex flex-col justify-between overflow-hidden relative group">
                <!-- En-tête de la carte -->
                <div class="p-5 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-400 tracking-wider">
                            <i class="las la-calendar-alt text-sm"></i> {{ $rapport->report_date->format('d/m/Y') }}
                        </span>

                        <!-- Badge Statut -->
                        <span class="px-2.5 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider flex items-center gap-1 {{ $statusConfig['bg'] }}">
                            <i class="las {{ $statusConfig['icon'] }} text-xs"></i> {{ $statusConfig['label'] }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-base font-black text-gray-800 capitalize">
                            {{ $rapport->full_title }}
                        </h3>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">
                            <i class="las la-clock"></i> {{ count($rapport->activities) }} activité(s) indexée(s) ({{ $rapport->activities->sum('duration') }}h)
                        </p>
                    </div>

                    <!-- Aperçu Réalisations -->
                    <div class="pt-2">
                        <span class="text-[10px] font-bold text-gray-500 uppercase block tracking-wider">Dernières réalisations :</span>
                        <p class="text-xs text-gray-600 line-clamp-2 mt-1 leading-relaxed italic">
                            "{{ $rapport->achievements ?? 'Aucune description rédigée...' }}"
                        </p>
                    </div>
                </div>

                <!-- Pied de la carte (Actions) -->
                <div class="bg-gray-50/70 border-t border-gray-100 px-5 py-3 flex justify-between items-center gap-2">
                    <span class="text-[10px] text-gray-400">
                        @if($rapport->submitted_at)
                            Soumis le {{ $rapport->submitted_at->format('d/m à H:i') }}
                        @else
                            Non soumis (Brouillon)
                        @endif
                    </span>

                    <div class="flex items-center gap-2">
                        <!-- Éditer / Mettre à jour -->
                        <a href="{{ route('rapports.update', ['reportId' => $rapport->id]) }}" class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 shadow-3xs hover:bg-gray-50 hover:text-blue-600 transition flex items-center gap-1">
                            <i class="las la-edit text-sm"></i> Editer
                        </a>

                        <a href="{{ route('rapports.print', ['reportId' => $rapport->id]) }}"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 hover:text-blue-600 hover:border-blue-300 transition shadow-2xs">
                            <i class="las la-print text-sm"></i>
                            Imprimer le rapport
                        </a>

                        <!-- Supprimer -->
                        <x-ui.button variant="danger" size="sm" wire:click="confirmDelete({{ $rapport->id }})" title="Retirer ce rapport">
                            <i class="las la-trash text-sm"></i>
                        </x-ui.button>
                    </div>
                </div>
            </div>
        @empty
            <!-- Message affiché si la liste des rapports est vide -->
            <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-12">
                <p class="text-xs text-gray-400 max-w-xs mx-auto">
                    Ajustez vos filtres de recherche ou cliquez sur le bouton en haut à droite pour générer votre premier livrable.
                </p>
            </div>
        @endforelse
    </div>

    <!-- Liens de pagination réactifs -->
    <div class="mt-5">
        <x-ui.pagination :paginator="$reports" />
    </div>

    <!-- Modale de Confirmation de Suppression de Rapport -->
    <x-ui.modal-one id="delete-report-modal" title="Confirmation de suppression" size="sm">
        <div class="text-center py-2">
            <i class="las la-exclamation-triangle text-red-500 text-5xl block mb-3 animate-pulse"></i>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Supprimer le rapport ?</h3>
            <p class="text-xs text-gray-500 px-2 leading-relaxed">
                Voulez-vous supprimer définitivement la ligne
                <br>
                <span class="font-bold text-gray-900">"{{ $deleteName }}"</span> ?
                <br><br>
                Cette opération est irréversible et détachera toutes les activités associées à cette période.
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
