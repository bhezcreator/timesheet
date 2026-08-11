<div class="p-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestion des activités
        </h2>
    </x-slot>

    <div class="w-full">
        <!-- Alertes de session -->
        @error('permission')
            <x-ui.alert type="error" class="mb-4">
                {{ $message }}
            </x-ui.alert>
        @enderror

        @error('activity')
            <x-ui.alert type="error" class="mb-4">
                {{ $message }}
            </x-ui.alert>
        @enderror

        <!-- AFFICHAGE DU TABLEAU OU DE L'STATE VIDE -->
        @if(!$timesheets->count() && empty($search) && empty($filterMonth) && empty($filterYear) && empty($filterStatus))
            <x-ui.empty-state title="Aucune activité déclarée" description="Aucune donnée." icon="las la-calendar-times">
                <x-slot:action>
                    <a href="{{ route('activities.create') }}" wire:navigate>
                        <x-ui.button>
                            <i class="las la-plus mr-1"></i> Déclarer une activité
                        </x-ui.button>
                    </a>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <x-ui.table :columns="['N°', 'Date', 'Intitulé', 'Projet / Sous-Projet', 'Type', 'Durée', 'Actions']">
                <x-slot:header>
                    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-4 sm:mb-6">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                            Mon journal d'activités
                        </h1>
                        <a href="{{ route('activities.create') }}" wire:navigate class="w-full sm:w-auto justify-center">
                            <x-ui.button class="w-full xs:w-auto justify-center">
                                <i class="las la-plus mr-1.5 sm:mr-2"></i>
                                <span class="hidden xs:inline text-sm sm:text-base">Déclarer une activité</span>
                                <span class="inline xs:hidden text-sm">Nouvelle</span>
                            </x-ui.button>
                        </a>
                    </div>

                    <!-- BARRE DE FILTRES MODERNE & RESPONSIVE STANDARDISÉE -->
                    <div class="bg-white p-2 rounded-xl border border-gray-200 shadow-xs mb-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">

                            <!-- Filtre 1 : Recherche par Titre (Saisie libre) -->
                            <div>
                                <x-ui.forms.input
                                    name="search"
                                    wire:model.live="search"
                                    placeholder="Rechercher par intitulé..."
                                />
                            </div>

                            <!-- Filtre Mois -->
                            <div class="relative w-full group" x-data="{ val: @entangle('filterMonth') }">
                                <select wire:model.live="filterMonth"
                                        class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition duration-150">
                                    <option value="">Tous les mois</option>
                                    @foreach($months as $num => $name)
                                        <option value="{{ $num }}">{{ $name }}</option>
                                    @endforeach
                                </select>

                                <!-- Bouton Réinitialiser (Visible si une option est choisie) -->
                                <div x-show="val" x-cloak class="absolute inset-y-0 right-3 flex items-center">
                                    <button type="button" @click="val = ''" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                                        <i class="las la-times-circle text-sm"></i>
                                    </button>
                                </div>

                                <!-- Flèche personnalisée (Masquée si une option est choisie) -->
                                <div x-show="!val" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                                    <i class="las la-angle-down text-xs"></i>
                                </div>
                            </div>

                            <!-- Filtre Année -->
                            <div class="relative w-full group" x-data="{ val: @entangle('filterYear') }">
                                <select wire:model.live="filterYear"
                                        class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition duration-150">
                                    <option value="">Toutes les années</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>

                                <!-- Bouton Réinitialiser (Visible si une option est choisie) -->
                                <div x-show="val" x-cloak class="absolute inset-y-0 right-3 flex items-center">
                                    <button type="button" @click="val = ''" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                                        <i class="las la-times-circle text-sm"></i>
                                    </button>
                                </div>

                                <!-- Flèche personnalisée (Masquée si une option est choisie) -->
                                <div x-show="!val" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                                    <i class="las la-angle-down text-xs"></i>
                                </div>
                            </div>

                            <!-- Filtre Statut -->
                            <div class="relative w-full group" x-data="{ val: @entangle('filterStatus') }">
                                <select wire:model.live="filterStatus"
                                        class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition duration-150">
                                    <option value="">Tous les statuts</option>
                                    <option value="brouillon">Brouillon</option>
                                    <option value="soumis">En attente de validation</option>
                                    <option value="approuvé">Approuvé</option>
                                    <option value="rejeté">Rejeté / À corriger</option>
                                </select>

                                <!-- Bouton Réinitialiser (Visible si une option est choisie) -->
                                <div x-show="val" x-cloak class="absolute inset-y-0 right-3 flex items-center">
                                    <button type="button" @click="val = ''" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                                        <i class="las la-times-circle text-sm"></i>
                                    </button>
                                </div>

                                <!-- Flèche personnalisée (Masquée si une option est choisie) -->
                                <div x-show="!val" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                                    <i class="las la-angle-down text-xs"></i>
                                </div>
                            </div>

                        </div>
                    </div>

                    @if(session('success'))
                        <x-ui.alert type="success" class="mb-4">
                            {{ session('success') }}
                        </x-ui.alert>
                    @endif
                </x-slot:header>

                <tbody>
                    @foreach($timesheets as $activite)
                        <tr class="hover:bg-gray-50/70 transition-colors" wire:key="activity-row-{{ $activite->id }}">
                            <!-- Index dynamique -->
                            <td class="px-6 py-4 text-xs font-semibold text-gray-400 align-middle">
                                {{ ($timesheets->currentPage() - 1) * $timesheets->perPage() + $loop->iteration }}
                            </td>

                            <!-- Date de l'activité -->
                            <td class="px-6 py-4 text-xs font-bold text-gray-900 align-middle whitespace-nowrap">
                                {{ $activite->activity_date ? $activite->activity_date->format('d/m/Y') : 'N/A' }}
                            </td>

                            <!-- Titre & Info de Rejet si présent -->
                            <td class="px-6 py-4 text-xs font-medium text-gray-800 align-middle max-w-xs">
                                <div class="truncate font-semibold" title="{{ $activite->titre }}">{{ $activite->titre }}</div>
                                @if($activite->status === 'rejeté' && $activite->rejection_reason)
                                    <div class="mt-1 text-[10px] text-red-600 bg-red-50 p-1.5 rounded border border-red-100 font-normal">
                                        <i class="las la-exclamation-circle font-bold"></i> <strong>Motif :</strong> {{ $activite->rejection_reason }}
                                    </div>
                                @endif
                            </td>

                            <!-- Arborescence Projet / Sous-Projet -->
                            <td class="px-6 py-4 text-xs align-middle max-w-xs">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-bold text-gray-900 truncate"><i class="las la-folder text-blue-500"></i> {{ $activite->project->code }} - {{ $activite->project->name }}</span>
                                    @if($activite->subProject)
                                        <span class="text-gray-400 text-[10px] truncate pl-3"><i class="las la-sitemap"></i> {{ $activite->subProject->name }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Type Imputation -->
                            <td class="px-6 py-4 text-xs align-middle whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-medium border border-gray-200 text-[10px]">
                                    {{ $activite->activityType->name }}
                                </span>
                            </td>

                            <!-- Durée formatée -->
                            <td class="px-6 py-4 text-xs font-mono font-black text-gray-900 align-middle whitespace-nowrap">
                                {{ floor($activite->duration) }}h : {{ round(($activite->duration - floor($activite->duration)) * 60) }}m
                            </td>

                            <!-- Statut Badge Coloré -->
{{--                             <td class="px-6 py-4 text-xs align-middle whitespace-nowrap">
                                @if($activite->status === 'brouillon')
                                    <x-ui.badge variant="default">Brouillon</x-ui.badge>
                                    <x-ui.badge variant="warning">En attente</x-ui.badge>
                                @elseif($activite->status === 'soumis')
                                    <x-ui.badge variant="info">Soumis</x-ui.badge>
                                @elseif($activite->status === 'approuvé')
                                    <x-ui.badge variant="success">Approuvé</x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger">Rejeté</x-ui.badge>
                                @endif
                            </td> --}}

                            <!-- Actions Contextuelles -->
                            <td class="px-6 py-4 text-xs space-x-1 whitespace-nowrap align-middle text-right">
                                <!-- Lien Modifier conditionnel (Seulement si modifiable) -->
                                @if(!$activite->monthlyReports->count())
                                    <a href="{{ route('activities.update', ['activityId' => $activite->id]) }}" wire:navigate class="inline-block">
                                        <x-ui.button variant="outline" size="sm" title="Modifier l'activité">
                                            <i class="las la-edit text-sm"></i>
                                        </x-ui.button>
                                    </a>

                                    <x-ui.button variant="danger" size="sm" wire:click="confirmDelete({{ $activite->id }})" title="Retirer cette ligne">
                                        <i class="las la-trash text-sm"></i>
                                    </x-ui.button>
                                @else
                                    <button disabled class="px-2.5 py-1.5 rounded-lg bg-gray-50 border border-gray-100 text-gray-300 text-xs cursor-not-allowed" title="Ligne verrouillée">
                                        <i class="las la-lock"></i> Fixe
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <!-- Liens de pagination réactifs -->
            <div class="mt-5">
                <x-ui.pagination :paginator="$timesheets" />
            </div>
        @endif
    </div>

    <!-- MODALE : Confirmation de suppression de la ligne d'activité -->
    <x-ui.modal
        :show="$showDeleteModal"
        id="delete-activity-modal"
        title="Confirmation de suppression"
        size="sm">
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
                <x-ui.button
                    variant="outline"
                    wire:click="closeDeleteModal"
                    wire:loading.attr="disabled"
                    wire:target="closeDeleteModal"
                >
                    Annuler
                </x-ui.button>

                <x-ui.button
                    variant="danger"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                >
                    <span wire:loading.remove wire:target="delete" class="flex items-center gap-1">
                        <i class="las la-trash"></i> Confirmer la suppression
                    </span>
                    <span wire:loading wire:target="delete" class="flex items-center gap-2">
                        <i class="las la-spinner la-spin"></i> Suppression...
                    </span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal>
</div>
