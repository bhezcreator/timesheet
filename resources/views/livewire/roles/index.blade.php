<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestion des rôles
        </h2>
    </x-slot>

    <div class="w-full mt-0">
        <!-- Alertes de session -->
        @if(session('success'))
            <x-ui.alert type="success" class="mb-4 mt-8">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @error('permission')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert>
        @enderror

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

        <!-- Liste des rôles -->
        @if(!$roles->count() && empty($search))
            <x-ui.empty-state title="Aucun rôle disponible" description="Créez et configurez votre premier rôle d'accès utilisateur." icon="las la-shield-alt">
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Créer un rôle
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <x-ui.table :columns="['N°', 'Nom du Rôle', 'Permissions assignées', 'Type d\'accès', 'Actions']">
                <x-slot:header>
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-2xl font-bold text-gray-900">
                            Rôles du système
                        </h1>
                        <x-ui.button wire:click="openModal">
                            <i class="las la-plus mr-1"></i> Nouveau rôle
                        </x-ui.button>
                    </div>

                    <x-ui.forms.input wire:model.live="search" placeholder="Recherche sur l'intitulé du rôle..." />
                </x-slot:header>

                <tbody>
                    @foreach($roles as $role)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="role-{{ $role->id }}">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-400 align-top">
                                {{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900 align-top">
                                {{ $role->name }}
                            </td>

                            <td class="px-6 py-4 text-sm max-w-xl align-top">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($role->permissions as $perm)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $perm->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">Aucune permission</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm align-top">
                                <x-ui.badge variant="success">
                                    {{ $role->guard_name }}
                                </x-ui.badge>
                            </td>

                            <td class="px-6 py-4 text-sm space-x-1 whitespace-nowrap align-top text-right">
                                <x-ui.button variant="outline" wire:click="edit({{ $role->id }})" title="Modifier le rôle">
                                    <i class="las la-edit"></i>
                                </x-ui.button>
                                <x-ui.button variant="danger" wire:click="confirmDelete({{ $role->id }})" title="Supprimer le rôle">
                                    <i class="las la-trash"></i>
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <div class="mt-5">
                <x-ui.pagination :paginator="$roles" />
            </div>
        @endif
    </div>

    <!-- MODALE : Création/Modification -->
    <x-ui.modal
        id="role-modal"
        :show="$showModal"
        title="{{ $roleId ? 'Modifier intitulé et les droits' : 'Ajouter un nouveau rôle' }}"
        size="xl"
        subtitle="{{ $roleId ? 'Modifiez le rôle et ses permissions associées' : 'Créez un nouveau rôle avec ses permissions' }}"
    >
        <div class="space-y-6">
            <x-ui.forms.input
                label="Nom du rôle"
                name="name"
                wire:model="name"
                placeholder="Ex: agent-saisie, comptable, admin"
            />
            <x-ui.forms.error name="name" />

            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-1">
                    <i class="las la-key text-gray-500 text-lg"></i> Attribuer des permissions à ce rôle
                </h4>

                @if($allPermissions->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-64 overflow-y-auto p-1 bg-gray-50 rounded-xl border border-gray-200/50">
                        @foreach($allPermissions as $perm)
                            <label class="relative flex items-start p-3 rounded-lg border border-gray-200 bg-white hover:bg-blue-50/30 transition cursor-pointer select-none" wire:key="perm-choice-{{ $perm->id }}">
                                <div class="flex h-5 items-center">
                                    <input
                                        type="checkbox"
                                        value="{{ $perm->id }}"
                                        wire:model="selectedPermissions"
                                        id="perm-{{ $perm->id }}"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                    >
                                </div>
                                <div class="ml-3 text-xs">
                                    <span class="font-medium text-gray-700 block tracking-tight">{{ $perm->name }}</span>
                                    <span class="text-gray-400 font-normal">Garde: {{ $perm->guard_name }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-yellow-600 bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                        Aucune permission n'est actuellement créée dans le système.
                    </p>
                @endif
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
                        <i class="las la-save mr-1"></i> Sauvegarder
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
        id="delete-role-modal"
        title="Confirmation de suppression"
        size="sm"
    >
        <div class="text-center py-2">
            <i class="las la-exclamation-triangle text-red-500 text-5xl block mb-3 animate-pulse"></i>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Supprimer le rôle ?</h3>
            <p class="text-sm text-gray-500 px-2">
                Êtes-vous sûr de vouloir supprimer définitivement le rôle <span class="font-bold text-gray-900">"{{ $deleteName }}"</span> ? Les utilisateurs associés perdront ces privilèges.
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
