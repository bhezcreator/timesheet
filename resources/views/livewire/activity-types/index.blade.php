<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestion des types d'activités
        </h2>
    </x-slot>

    <div class="w-full">
        <!-- Alertes de session -->
        @if(session('success'))
            <x-ui.alert type="success" class="mb-4 mt-8">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <!-- Erreur exclusive sur l'action de permission ou contrainte critique -->
   {{--      @error('permission')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert>
        @enderror

        @error('activity_type')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert>
        @enderror --}}

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

        <!-- Liste des types sous forme de tableau filtrable -->
        @if(!$activityTypes->count() && empty($search))
            <x-ui.empty-state title="Aucun type d'activité" description="Créez et configurez votre premier type d'activité pour catégoriser vos données." icon="las la-tags">
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Créer un type
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <x-ui.table :columns="['N°', 'Couleur', 'Intitulé', 'Description', 'Activités liées', 'Statut', 'Actions']">
                <x-slot:header>
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-1xl font-bold text-gray-900">
                            Types d'activités
                        </h1>
                        <x-ui.button wire:click="openModal">
                            <i class="las la-plus mr-1"></i> Nouveau type
                        </x-ui.button>
                    </div>

                    <x-ui.forms.input wire:model.live="search" placeholder="Recherche sur l'intitulé ou la description..." />
                </x-slot:header>

                <tbody>
                    @foreach($activityTypes as $type)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="type-{{ $type->id }}">
                            <!-- Index dynamique -->
                            <td class="px-6 py-4 text-sm font-semibold text-gray-400 align-top">
                                {{ ($activityTypes->currentPage() - 1) * $activityTypes->perPage() + $loop->iteration }}
                            </td>

                            <!-- Pastille de couleur -->
                            <td class="px-6 py-4 text-sm align-top">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full border border-gray-300 shadow-sm inline-block" style="background-color: {{ $type->color }}"></span>
                                    <code class="text-xs text-gray-500 uppercase">{{ $type->color }}</code>
                                </div>
                            </td>

                            <!-- Nom -->
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 align-top">
                                {{ $type->name }}
                            </td>

                            <!-- Description -->
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs align-top truncate" title="{{ $type->description }}">
                                {{ $type->description ?? 'Aucune description' }}
                            </td>

                            <!-- Compteur d'activités liées -->
                            <td class="px-6 py-4 text-sm font-medium text-gray-600 align-top">
                                <span class="px-2 py-1 rounded bg-gray-100 text-gray-800 text-xs">
                                    {{ $type->activities_count }} relation(s)
                                </span>
                            </td>

                            <!-- Statut Actif -->
                            <td class="px-6 py-4 text-sm align-top">
                                <x-ui.badge :variant="$type->is_active ? 'success' : 'danger'">
                                    {{ $type->is_active ? 'Actif' : 'Inactif' }}
                                </x-ui.badge>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-sm space-x-1 whitespace-nowrap align-top text-right">
                                <x-ui.button variant="outline" wire:click="edit({{ $type->id }})" title="Modifier le type">
                                    <i class="las la-edit"></i>
                                </x-ui.button>
                                <x-ui.button variant="danger" wire:click="confirmDelete({{ $type->id }})" title="Supprimer le type">
                                    <i class="las la-trash"></i>
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <!-- Pagination -->
            <div class="mt-5">
                <x-ui.pagination :paginator="$activityTypes" />
            </div>
        @endif
    </div>

    <!-- Modale A : Création et Modification -->
    <x-ui.modal-one id="activity-type-modal" title="{{ $activityTypeId ? 'Modifier le type activité' : 'Ajouter un nouveau type activité' }}" size="xl">
        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Nom -->
            <x-ui.forms.input
                label="Nom du type d'activité"
                name="name"
                wire:model="name"
                required
                placeholder="Ex: Réunion, Maintenance, Formation"
            />
            <x-ui.forms.error name="name" />

            <!-- Description -->
            <div>
                <x-ui.forms.textarea
                wire:model="description"
                    name="description"
                    label="Description"
                    wire:model="description"
                    rows="3"
                    placeholder="Optionnel : Ajoutez des détails sur l'usage de ce type..."
                />
                <x-ui.forms.error name="description" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-100 pt-4">
                <!-- Sélecteur de couleur -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Couleur d'identification</label>
                    <div class="flex items-center gap-3">
                        <input
                            type="color"
                            wire:model="color"
                            class="h-10 w-16 cursor-pointer rounded-lg border border-gray-300 p-0.5 bg-white"
                        >
                        <x-ui.forms.input
                            name="color"
                            wire:model="color"
                            placeholder="#FFFFFF"
                            class="w-full"
                        />
                    </div>
                    <x-ui.forms.error name="color" />
                </div>

                <!-- Bascule de statut (is_active) -->
                <div class="flex flex-col justify-center">
                    <label class="text-sm font-medium text-gray-700 mb-2">Disponibilité du type</label>
                    <label class="relative inline-flex items-center cursor-pointer select-none mt-2">
                        <input
                            type="checkbox"
                            wire:model="is_active"
                            class="sr-only peer"
                        >
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Rendre ce type actif</span>
                    </label>
                    <x-ui.forms.error name="is_active" />
                </div>
            </div>

            <!-- Pied de modale pour actions -->
            <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 mt-6">
                <x-ui.button type="button" variant="outline" wire:click="closeModal">
                    Annuler
                </x-ui.button>
                <x-ui.button wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="las la-save mr-1"></i> Enregistrer le projet</span>
                    <span wire:loading><i class="las la-spinner la-spin mr-1"></i> Traitement...</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal-one>

    <!-- Modale B : Validation de la suppression destructive -->
    <x-ui.modal-one id="delete-activity-type-modal" title="Confirmer la suppression" size="md">
        <div class="space-y-4">
                        <div class="flex items-center gap-3 text-red-600">
                <i class="las la-exclamation-triangle text-3xl"></i>
                <h3 class="text-lg font-bold">Attention : Action irréversible</h3>
            </div>

            <p class="text-sm text-gray-600">
                Êtes-vous sûr de vouloir supprimer définitivement le type d'activité
                <strong class="text-gray-900">"{{ $deleteName }}"</strong> ?
            </p>

            <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                <x-ui.button variant="outline" wire:click="closeModal">
                    Annuler
                </x-ui.button>
                <x-ui.button variant="danger" wire:click="delete({{ $deleteId ?? 0 }})">
                    Supprimer définitivement
                </x-ui.button>
            </div>
        </div>
    </x-ui.modal-one>
</div>
