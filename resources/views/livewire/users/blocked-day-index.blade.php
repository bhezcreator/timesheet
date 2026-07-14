<div class="py-0">

    <div class="w-full">
        <!-- Alertes de session -->
        @if(session('success'))
            <x-ui.alert type="success" class="mb-4 mt-8">
                {{ session('success') }}
            </x-ui.alert>
            <br>
        @endif

        <!-- Affichage global des erreurs de validation ($errors) -->
        @if($errors->any())
            <x-ui.alert type="error" class="mb-4 mt-8">
                <div class="flex flex-col gap-1">
                    <span class="font-bold text-sm mb-1">Message d'erreur :</span>
                    <ul class="list-disc list-inside text-xs space-y-0.5 opacity-90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </x-ui.alert>
            <br>
        @endif

        <!-- Liste des jours bloqués sous forme de tableau filtrable -->
        @if(!$blockedDays->count() && empty($search))
            <x-ui.empty-state title="Aucun jour bloqué" description="Configurez vos jours fériés, congés entreprise ou périodes de maintenance." icon="las la-calendar-times">
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Bloquer un jour
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <x-ui.table :columns="['N°', 'Date', 'Intitulé', 'Catégorie / Type', 'Statut', 'Actions']">
                <x-slot:header>
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-2xl font-bold text-gray-900">
                            Calendrier des jours verrouillés
                        </h1>
                        <x-ui.button wire:click="openModal">
                            <i class="las la-plus mr-1"></i> Bloquer une date
                        </x-ui.button>
                    </div>

                    <x-ui.forms.input wire:model.live="search" placeholder="Recherche sur l'intitulé ou la catégorie..." />
                </x-slot:header>

                <tbody>
                    @foreach($blockedDays as $day)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="day-{{ $day->id }}">
                            <!-- Index dynamique -->
                            <td class="px-6 py-4 text-sm font-semibold text-gray-400 align-top">
                                {{ ($blockedDays->currentPage() - 1) * $blockedDays->perPage() + $loop->iteration }}
                            </td>

                            <!-- Date formatée -->
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 align-top whitespace-nowrap">
                                {{ $day->date ? $day->date->format('d/m/Y') : 'N/A' }}
                            </td>

                            <!-- Nom de l'événement -->
                            <td class="px-6 py-4 text-sm font-medium text-gray-700 align-top">
                                {{ $day->name }}
                            </td>

                            <!-- Type / Énumération -->
                            <td class="px-6 py-4 text-sm align-top">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $day->type }}
                                </span>
                            </td>

                            <!-- Statut (Actif/Inactif) -->
                            <td class="px-6 py-4 text-sm align-top">
                                <x-ui.badge :variant="$day->is_active ? 'success' : 'danger'">
                                    {{ $day->is_active ? 'Verrouillé' : 'Inactif' }}
                                </x-ui.badge>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-sm space-x-1 whitespace-nowrap align-top text-right">
                                <x-ui.button variant="outline" wire:click="edit({{ $day->id }})" title="Modifier">
                                    <i class="las la-edit"></i>
                                </x-ui.button>
                                <x-ui.button variant="danger" wire:click="confirmDelete({{ $day->id }})" title="Supprimer">
                                    <i class="las la-trash"></i>
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <!-- Liens de pagination réactifs -->
            <div class="mt-5">
                <x-ui.pagination :paginator="$blockedDays" />
            </div>
        @endif
    </div>

    <!-- Modale A : Création et Modification -->
    <x-ui.modal-one id="blocked-day-modal" title="{{ $blockedDayId ? 'Modifier la date verrouillée' : 'Verrouiller une nouvelle date' }}" size="xl">
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Sélecteur de date -->
                <div class="col-span-1">
                    <x-ui.forms.input
                        type="date"
                        label="Date à verrouiller"
                        name="date"
                        wire:model="date"
                    />
                    <x-ui.forms.error name="date" />
                </div>
                <!-- Libellé de l'événement -->
                <div class="col-span-2">
                    <x-ui.forms.input
                        label="Nom / Motif du blocage"
                        name="name"
                        wire:model="name"
                        placeholder="Ex: Noël, Tabaski, Maintenance Serveur..."
                    />
                    <x-ui.forms.error name="name" />
                </div>
            </div>

            <!-- Grille Moderne de sélection pour l'Énumération (Type) avec zone défilante -->
            <div class="border-t border-gray-100 pt-4">
                <label class="block text-sm font-semibold text-gray-900 mb-3">Catégorie de blocage</label>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-64 overflow-y-auto p-1 bg-gray-50 rounded-xl border border-gray-200/50">
                    @foreach($availableTypes as $typeOption)
                        <!-- Structure identique à vos permissions : simple, native et stable -->
                        <label
                            wire:key="type-option-{{ $loop->index }}"
                            class="relative flex items-start p-3 rounded-lg border transition cursor-pointer select-none shadow-sm {{ $type === $typeOption ? 'border-blue-600 bg-blue-50/30' : 'border-gray-200 bg-white hover:bg-blue-50/30' }}"
                        >
                            <div class="flex h-5 items-center">
                                <input
                                    type="radio"
                                    value="{{ $typeOption }}"
                                    wire:model="type" {{-- Liaison standard sans .live pour éviter l'envoi intempestif au serveur avant le clic sur Save --}}
                                    name="blocked_day_type"
                                    id="type-{{ $loop->index }}"
                                    class="h-4 w-4 rounded-full border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                >
                            </div>
                            <div class="ml-3 text-xs">
                                <span class="font-medium text-gray-700 block tracking-tight">{{ $typeOption }}</span>
                                <span class="text-gray-400 font-normal">Option système</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                <x-ui.forms.error name="type" />
            </div>

            <!-- Toggle de statut d'activité -->
            <div class="flex flex-col justify-center border-t border-gray-100 pt-4">
                <label class="text-sm font-semibold text-gray-900 mb-2">État du verrouillage</label>
                <label class="relative inline-flex items-center cursor-pointer select-none mt-1">
                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700">Appliquer activement la restriction sur les feuilles de temps</span>
                </label>
                <x-ui.forms.error name="is_active" />
            </div>

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button variant="outline" wire:click="closeModal" data-close-modal>
                        Annuler
                    </x-ui.button>

                    <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="las la-save mr-1"></i> Sauvegarder
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="las la-spinner la-spin mr-1"></i> Traitement...
                        </span>
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </form>
    </x-ui.modal-one>

    <!-- Modale B : Validation de la suppression destructive -->
    <x-ui.modal-one id="delete-blocked-day-modal" title="Débloquer la date" size="md">
        <div class="space-y-4">
            <div class="flex items-center justify-center gap-3 text-red-600">
                <i class="las la-exclamation-triangle text-3xl"></i>
                <h3 class="text-lg font-bold">Attention : Levée de restriction</h3>
            </div>

            <p class="text-sm text-gray-600 flex flex-col items-center justify-center gap-3">
                Êtes-vous sûr de vouloir supprimer définitivement la date verrouillée
                <strong class="text-gray-900">"{{ $deleteName }}" ?</strong>
                Les utilisateurs pourront à nouveau soumettre des heures à cette date.
            </p>

            <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                <x-ui.button variant="outline" wire:click="closeModal" data-close-modal>
                    Annuler
                </x-ui.button>
                <x-ui.button variant="danger" wire:click="delete({{ $deleteId ?? 0 }})" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="las la-trash"></i>
                        Confirmer la suppression
                    </span>

                    <span wire:loading class="flex items-center gap-2">
                        <x-ui.spinner size="sm" color="white"/>
                        Suppression...
                    </span>
                </x-ui.button>
            </div>
        </div>
    </x-ui.modal-one>
</div>
