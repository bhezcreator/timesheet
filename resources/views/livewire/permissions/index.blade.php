<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestion des permissions
        </h2>
    </x-slot>

    <div class="w-full">
        <!-- Alertes de session -->
        @if(session('success'))
            <x-ui.alert type="success" class="mb-4 mt-8">
                {{ session('success') }}
            </x-ui.alert>
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

        <!-- Liste des données ou État vide -->
        @if($permissions->count())
            <x-ui.table :columns="['N°', 'Nom', 'Type d\'accès', 'Créée le', 'Actions']">
                <x-slot:header>
                    <!-- En-tête de section -->
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-2xl font-bold text-gray-900">
                            Permissions
                        </h1>
                        <x-ui.button wire:click="openModal">
                            <i class="las la-plus mr-1"></i> Nouvelle permission
                        </x-ui.button>
                    </div>

                    <x-ui.forms.input wire:model.live="search" placeholder="Recherche sur le nom..." />
                </x-slot:header>

                <tbody>
                    @foreach($permissions as $index => $permission)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="permission-{{ $permission->id }}">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-400">
                                {{ ($permissions->currentPage() - 1) * $permissions->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $permission->name }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <x-ui.badge variant="success">
                                    {{ $permission->guard_name }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $permission->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm space-x-1 text-right">
                                <x-ui.button variant="outline" wire:click="edit({{ $permission->id }})" title="Modifier">
                                    <i class="las la-edit"></i>
                                </x-ui.button>
                                <x-ui.button variant="danger" wire:click="confirmDelete({{ $permission->id }})" title="Supprimer">
                                    <i class="las la-trash"></i>
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <!-- Pagination -->
            <div class="mt-5">
                <x-ui.pagination :paginator="$permissions" />
            </div>
        @else
            <x-ui.empty-state title="Aucune permission" description="Créez votre première permission." icon="las la-key">
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Créer
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @endif
    </div>

    <!-- Modale de Création / Édition -->
    <x-ui.modal-one id="permission-modal" title="{{ $permissionId ? 'Modifier permission' : 'Nouvelle permission' }}">
        <div class="space-y-4">
            <x-ui.forms.input
                label="Nom de la permission"
                name="name"
                wire:model="name"
                placeholder="Ex: create-project"
            />
            <x-ui.forms.error name="name" />
        </div>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <x-ui.button variant="outline" wire:click="closeModal" data-close-modal>
                    Annuler
                </x-ui.button>

                <x-ui.button wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="las la-save mr-1"></i> Sauvegarder
                    </span>
                    <span wire:loading>
                        <i class="las la-spinner la-spin mr-1"></i> Chargement...
                    </span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal-one>

    <x-ui.modal-one id="delete-permission-modal" title="Confirmation">
        <div class="flex flex-col items-center text-center py-2">

            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-5">
                <i class="las la-trash-alt text-3xl text-red-600"></i>
            </div>

            <h3 class="text-lg font-semibold text-gray-900">
                Confirmer la suppression
            </h3>

            <p class="mt-2 text-gray-500">
                Voulez-vous vraiment supprimer la permission
            </p>

            <div class="mt-4 inline-flex items-center rounded-lg bg-gray-100 px-4 py-2">
                <i class="las la-key text-gray-500 mr-2"></i>
                <span class="font-semibold text-gray-800">
                    {{ $deleteName }}
                </span>
            </div>

            <p class="mt-5 text-sm text-red-600 bg-red-100 px-4 py-2 rounded-lg">
                Cette action est définitive et ne peut pas être annulée.
            </p>

        </div>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <x-ui.button
                    variant="outline"
                    data-close-modal
                >
                    Annuler
                </x-ui.button>

                <x-ui.button
                    variant="danger"
                    wire:click="delete({{ $deleteId ?? 0 }})"
                    wire:loading.attr="disabled"
                >

                    <span wire:loading.remove>
                        <i class="las la-trash"></i>
                        Supprimer
                    </span>

                    <span wire:loading class="flex items-center gap-2">
                        <x-ui.spinner size="sm" color="white"/>
                        Suppression...
                    </span>

                </x-ui.button>

            </div>

        </x-slot:footer>

    </x-ui.modal-one>
</div>
