<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestion des bureaux
        </h2>
    </x-slot>

    <div class="w-full mt-0">
        <!-- Alertes de permission -->
        @error('permission')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert>
        @enderror

        @error('office')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert>
        @enderror

        <!-- Liste des bureaux -->
        @if(!$offices->count() && empty($search))
            <x-ui.empty-state 
                title="Aucun bureau configuré" 
                description="Créez votre premier bureau pour commencer à gérer les présences et les horaires." 
                icon="las la-building"
            >
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Créer un bureau
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <x-ui.table :columns="['N°', 'Bureau', 'Code', 'Localisation', 'Rayon', 'Statut', 'Actions']">
                <x-slot:header>
                    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-3">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate flex items-center gap-2">
                            <i class="las la-building text-indigo-600 text-2xl"></i>
                            Bureaux du système
                        </h1>
                        <x-ui.button wire:click="openModal" class="w-full sm:w-auto justify-center">
                            <i class="las la-plus mr-1.5 sm:mr-2"></i>
                            <span class="hidden xs:inline text-sm sm:text-base">Nouveau bureau</span>
                            <span class="inline xs:hidden text-sm">Nouveau</span>
                        </x-ui.button>
                    </div>

                    <x-ui.forms.input 
                        wire:model.live="search" 
                        placeholder="Recherche par nom, code ou adresse..." 
                    />

                    @if(session('success'))
                        <br>
                        <x-ui.alert type="success" class="mb-4 mt-8">
                            {{ session('success') }}
                        </x-ui.alert>
                        <br>
                    @endif
                </x-slot:header>

                <tbody>
                    @foreach($offices as $office)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="office-{{ $office->id }}">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-400 align-top">
                                {{ ($offices->currentPage() - 1) * $offices->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 text-sm align-top">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                                        <i class="las la-building text-indigo-500 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $office->name }}</p>
                                        <p class="text-xs text-gray-400 truncate max-w-[200px]">{{ $office->address ?? 'Aucune adresse' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm align-top">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-mono font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                    {{ $office->code }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm align-top">
                                @if($office->latitude && $office->longitude)
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-xs text-gray-600 flex items-center gap-1">
                                            <i class="las la-map-marker text-indigo-400"></i>
                                            {{ number_format($office->latitude, 6) }}, {{ number_format($office->longitude, 6) }}
                                        </span>
                                        <span class="text-xs text-gray-400 flex items-center gap-1">
                                            <i class="las la-route"></i>
                                            Rayon: {{ $office->radius }}m
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Non défini</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm align-top">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                    <i class="las la-ruler-combined text-blue-500"></i>
                                    {{ $office->radius ?? 100 }} m
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm align-top">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $office->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 
                                            {{ $office->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        {{ $office->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm space-x-1 whitespace-nowrap align-top text-right">
                                <x-ui.button 
                                    variant="outline" 
                                    wire:click="toggleStatus({{ $office->id }})" 
                                    title="{{ $office->is_active ? 'Désactiver' : 'Activer' }}"
                                    class="!p-1.5"
                                >
                                    <i class="las {{ $office->is_active ? 'la-eye' : 'la-eye-slash' }}"></i>
                                </x-ui.button>
                                <x-ui.button 
                                    variant="outline" 
                                    wire:click="edit({{ $office->id }})" 
                                    title="Modifier le bureau"
                                    class="!p-1.5"
                                >
                                    <i class="las la-edit"></i>
                                </x-ui.button>
                                <x-ui.button 
                                    variant="danger" 
                                    wire:click="confirmDelete({{ $office->id }})" 
                                    title="Supprimer le bureau"
                                    class="!p-1.5"
                                >
                                    <i class="las la-trash"></i>
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <div class="mt-5">
                <x-ui.pagination :paginator="$offices" />
            </div>
        @endif
    </div>

    <!-- MODALE : Création/Modification -->
    <x-ui.modal
        id="office-modal"
        :show="$showModal"
        title="{{ $officeId ? 'Modifier le bureau' : 'Ajouter un nouveau bureau' }}"
        size="xl"
        subtitle="{{ $officeId ? 'Modifiez les informations du bureau' : 'Configurez un nouveau bureau pour la gestion des présences' }}"
    >
        <div class="space-y-6">
            <!-- Informations de base -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-ui.forms.input
                        label="Nom du bureau"
                        name="name"
                        wire:model="name"
                        placeholder="Ex: Siège social, Agence Paris..."
                        required
                    />
                    <x-ui.forms.error name="name" />
                </div>

                <div>
                    <x-ui.forms.input
                        label="Code du bureau"
                        name="code"
                        wire:model="code"
                        placeholder="Ex: SIEGE, PARIS-01..."
                        required
                        helper="Majuscules, chiffres, tirets et underscores uniquement"
                    />
                    <x-ui.forms.error name="code" />
                </div>
            </div>

            <div>
                <x-ui.forms.textarea
                    label="Adresse"
                    name="address"
                    wire:model="address"
                    placeholder="Adresse complète du bureau..."
                    rows="2"
                />
                <x-ui.forms.error name="address" />
            </div>

            <!-- Localisation GPS -->
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="las la-map-marked-alt text-indigo-500 text-lg"></i>
                    Localisation GPS
                    <span class="text-xs font-normal text-gray-400">(Optionnel pour les présences géolocalisées)</span>
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-ui.forms.input
                            label="Latitude"
                            name="latitude"
                            wire:model="latitude"
                            placeholder="Ex: 48.856614"
                            type="number"
                            step="any"
                        />
                        <x-ui.forms.error name="latitude" />
                    </div>

                    <div>
                        <x-ui.forms.input
                            label="Longitude"
                            name="longitude"
                            wire:model="longitude"
                            placeholder="Ex: 2.352222"
                            type="number"
                            step="any"
                        />
                        <x-ui.forms.error name="longitude" />
                    </div>

                    <div>
                        <x-ui.forms.input
                            label="Rayon (mètres)"
                            name="radius"
                            wire:model="radius"
                            placeholder="100"
                            type="number"
                            step="1"
                            helper="Distance en mètres depuis le point GPS"
                        />
                        <x-ui.forms.error name="radius" />
                    </div>
                </div>
            </div>

            <!-- Statut -->
            <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            wire:model="is_active"
                            class="sr-only peer"
                        >
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700">
                            <i class="las {{ $is_active ? 'la-check-circle text-emerald-500' : 'la-times-circle text-gray-400' }} mr-1"></i>
                            {{ $is_active ? 'Bureau actif' : 'Bureau inactif' }}
                        </span>
                    </label>
                    <span class="text-xs text-gray-400">(Les bureaux inactifs ne sont pas disponibles pour les pointages)</span>
                </div>
                <x-ui.forms.error name="is_active" />
            </div>
        </div>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <x-ui.button
                    variant="outline"
                    wire:click="closeModal"
                    wire:loading.attr="disabled"
                    wire:target="closeModal"
                >
                    <span wire:loading.remove wire:target="closeModal">Annuler</span>
                    <span wire:loading wire:target="closeModal">
                        <i class="las la-spinner la-spin mr-1"></i>
                    </span>
                </x-ui.button>

                <x-ui.button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">
                        <i class="las la-save mr-1"></i> {{ $officeId ? 'Mettre à jour' : 'Créer' }}
                    </span>
                    <span wire:loading wire:target="save">
                        <i class="las la-spinner la-spin mr-1"></i> Traitement...
                    </span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODALE : Confirmation de suppression -->
    <x-ui.modal
        :show="$showDeleteModal"
        id="delete-office-modal"
        title="Confirmation de suppression"
        size="sm"
    >
        <div class="text-center py-2">
            <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="las la-exclamation-triangle text-red-500 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Supprimer le bureau ?</h3>
            <p class="text-sm text-gray-500 px-2">
                Êtes-vous sûr de vouloir supprimer définitivement le bureau 
                <span class="font-bold text-gray-900">"{{ $deleteName }}"</span> ?
            </p>
            <p class="text-xs text-gray-400 mt-2 flex items-center justify-center gap-1">
                <i class="las la-info-circle"></i>
                Cette action est irréversible et supprimera toutes les données associées.
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
                    <span wire:loading.remove wire:target="delete">
                        <i class="las la-trash mr-1"></i>
                        Confirmer la suppression
                    </span>
                    <span wire:loading wire:target="delete">
                        <i class="las la-spinner la-spin mr-1"></i>
                        Suppression...
                    </span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal>
</div>