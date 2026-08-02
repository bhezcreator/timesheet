<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestion des personnels
        </h2>
    </x-slot>

    <div class="w-full">
        <!-- Alertes de session -->
        @if(session('success'))
            <x-ui.alert type="success" class="mb-4 mt-8">
                {{ session('success') }}
            </x-ui.alert>
            <br>
        @endif

        @error('permission')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert>
            <br>
        @enderror

        <!-- Tableau principal ou État vide -->
        @if(!$users->count() And empty($search))
                    <!-- État vide par défaut -->
            <x-ui.empty-state title="Aucun agent trouvé" description="Enregistrez vos collaborateurs et affectez leurs rôles de sécurité." icon="las la-users">
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Créer une fiche du personnel
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <x-ui.table :columns="['N°', 'Matricule', 'Personnel', 'Poste / Fonction', 'Superviseur', 'Rôles d\'accès', 'Statut', 'Actions']">
                <x-slot:header>
                    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-4 sm:mb-6">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                            Personnel
                        </h1>
                        <x-ui.button wire:click="openModal" class="w-full sm:w-auto justify-center">
                            <i class="las la-plus mr-1.5 sm:mr-2"></i>
                            <span class="hidden sm:inline text-sm sm:text-base">Ajouter un personnel</span>
                            <span class="inline sm:hidden text-sm">Ajouter</span>
                        </x-ui.button>
                    </div>

                    <x-ui.forms.input wire:model.live.debounce.300ms="search" placeholder="Recherche par matricule, nom, prénom ou email..." />
                </x-slot:header>

                <tbody>
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="user-row-{{ $user->id }}">
                            <!-- Indexation continue d'une page à l'autre -->
                            <td class="px-6 py-4 text-sm font-semibold text-gray-400">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </td>

                            <!-- Numéro matricule -->
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                <span class="font-mono bg-gray-100 px-2 py-1 rounded text-xs text-gray-600">{{ $user->num_order }}</span>
                            </td>

                            <!-- Bloc Identité -->
                            <td class="px-6 py-4 text-sm">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">{{ $user->first_name }} {{ $user->name }} {{ $user->last_name }}</span>
                                    <span class="text-xs text-gray-400">{{ $user->email }}</span>
                                </div>
                            </td>

                            <!-- Intitulé de poste -->
                            <td class="px-6 py-4 text-sm text-gray-700 font-medium">
                                {{ $user->job_title }}
                            </td>

                            <!-- Responsable hiérarchique -->
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $user->supervisor ? $user->supervisor->first_name . ' ' . $user->supervisor->name : '-' }}
                            </td>

                            <!-- Badges des Rôles affectés -->
                            <td class="px-6 py-4 text-sm">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @forelse($user->roles as $role)
                                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs font-semibold border border-blue-100">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">Aucun rôle</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- État du compte -->
                            <td class="px-6 py-4 text-sm">
                                @if($user->is_active)
                                    <x-ui.badge variant="success">Actif</x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger">Inactif</x-ui.badge>
                                @endif
                            </td>

                            <!-- Boutons de commande -->
                            <td class="px-6 py-4 text-sm space-x-1 whitespace-nowrap text-right">
                                <a href="{{ route('users.show', ['userId' => $user->id]) }}"
                                    wire:navigate class="inline-flex items-center justify-center bg-green-50 gap-1.5 px-3 py-2 text-sm font-medium rounded-xl border border-green-200 text-green-600 hover:text-green-600 hover:bg-green-50/50 hover:border-green-100 transition shadow-xs"
                                    title="Gérer les attributions de projets">
                                    <i class="las la-eye text-base text-blue"></i>
                                    <span>Fiche</span>
                                </a>

                                <a href="{{ route('users.attributes_projects', ['userId' => $user->id]) }}"
                                    wire:navigate class="inline-flex items-center justify-center bg-blue-50 gap-1.5 px-3 py-2 text-sm font-medium rounded-xl border border-blue-200 text-blue-700 hover:text-blue-600 hover:bg-blue-50/50 hover:border-blue-100 transition shadow-xs"
                                    title="Gérer les attributions de projets">
                                    <i class="las la-user-cog text-base text-blue"></i>
                                    <span>Attribution Projet</span>
                                </a>

                                <x-ui.button variant="secondary" wire:click="edit({{ $user->id }})" title="Éditer la fiche">
                                    <i class="las la-edit"></i>
                                </x-ui.button>

                                <x-ui.button variant="danger" wire:click="confirmDelete({{ $user->id }})" title="Supprimer le compte">
                                    <i class="las la-trash"></i>
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <!-- Navigation AJAX synchrone -->
            <div class="mt-5">
                <x-ui.pagination :paginator="$users" />
            </div>
        @endif
    </div>

    <!-- MODALE : Création/Modification d'utilisateur -->
    <x-ui.modal
        id="user-modal"
        :show="$showModal"
        title="{{ $userId ? 'Modifier les informations du personel' : 'Ajouter un nouvel personel' }}"
        size="xl"
        subtitle="{{ $userId ? 'Modifiez les informations et les rôles du personel' : 'Créez un nouveau compte personel avec ses rôles' }}">
        <div class="space-y-6">
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
            <!-- Grille d'informations personnelles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-ui.forms.input
                        label="Matricule"
                        name="num_order"
                        wire:model="num_order"
                        placeholder="Ex: EMP-001"
                    />
                    <x-ui.forms.error name="num_order" />
                </div>

                <div>
                    <x-ui.forms.input
                        label="Nom d'utilisateur"
                        name="name"
                        wire:model="name"
                        placeholder="Ex: Jean Dupont"
                    />
                    <x-ui.forms.error name="name" />
                </div>

                <div>
                    <x-ui.forms.input
                        label="Prénom"
                        name="first_name"
                        wire:model="first_name"
                        placeholder="Ex: Jean"
                    />
                    <x-ui.forms.error name="first_name" />
                </div>

                <div>
                    <x-ui.forms.input
                        label="Nom de famille"
                        name="last_name"
                        wire:model="last_name"
                        placeholder="Ex: Dupont"
                    />
                    <x-ui.forms.error name="last_name" />
                </div>

                <div>
                    <x-ui.forms.input
                        label="Fonction"
                        name="job_title"
                        wire:model="job_title"
                        placeholder="Ex: Secrétaire, Comptable, Directeur..."
                    />
                    <x-ui.forms.error name="job_title" />
                </div>

                <div>
                    <x-ui.forms.select
                        wire:model="supervisor_id"
                        name="supervisor_id"
                        label="Superviseur"
                        placeholder="Choisir le superviseur responsable..."
                        :selected="$supervisor_id"
                        :options="$supervisors->map(fn($s) => [
                            'value'       => (string)$s->id,
                            'label'       => $s->first_name . ' ' . $s->name,
                            'description' => $s->job_title,
                            'icon'        => 'las la-user-tie'
                        ])->toArray()"
                    />
                    <x-ui.forms.error name="supervisor_id" />
                </div>

                <div>
                    <x-ui.forms.input
                        label="Email"
                        name="email"
                        type="email"
                        wire:model="email"
                        placeholder="Ex: jean.dupont@exemple.com"
                    />
                    <x-ui.forms.error name="email" />
                </div>

                <div>
                    <x-ui.forms.input
                        label="{{ $userId ? 'Mot de passe (laissez vide pour conserver)' : 'Mot de passe' }}"
                        name="password"
                        type="password"
                        wire:model="password"
                        placeholder="{{ $userId ? 'Nouveau mot de passe...' : 'Mot de passe...' }}"
                    />
                    <x-ui.forms.error name="password" />
                </div>

                <div class="flex items-center space-x-3">
                    <x-ui.forms.toggle
                        label="Compte actif"
                        name="is_active"
                        wire:model="is_active"
                    />
                </div>
            </div>

            <!-- Sélection des rôles -->
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-1">
                    <i class="las la-user-tag text-gray-500 text-lg"></i> Attribuer des rôles à cet utilisateur
                </h4>

                @if($allRoles->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-64 overflow-y-auto p-1 bg-gray-50 rounded-xl border border-gray-200/50">
                        @foreach($allRoles as $role)
                            <label class="relative flex items-start p-3 rounded-lg border border-gray-200 bg-white hover:bg-purple-50/30 transition cursor-pointer select-none" wire:key="role-choice-{{ $role->id }}">
                                <div class="flex h-5 items-center">
                                    <input
                                        type="checkbox"
                                        value="{{ $role->id }}"
                                        wire:model="selectedRoles"
                                        id="role-{{ $role->id }}"
                                        class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer"
                                    >
                                </div>
                                <div class="ml-3 text-xs">
                                    <span class="font-medium text-gray-700 block tracking-tight">{{ $role->name }}</span>
                                    <span class="text-gray-400 font-normal">Garde: {{ $role->guard_name }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedRoles')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @else
                    <p class="text-xs text-yellow-600 bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                        Aucun rôle n'est actuellement créé dans le système. Créez d'abord des rôles avant d'attribuer des droits à cet utilisateur.
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
        id="delete-user-modal"
        title="Confirmation de suppression"
        size="sm"
    >
        <div class="text-center py-2">
            <i class="las la-exclamation-triangle text-red-500 text-5xl block mb-3 animate-pulse"></i>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Supprimer l'utilisateur ?</h3>
            <p class="text-sm text-gray-500 px-2">
                Êtes-vous sûr de vouloir supprimer définitivement l'utilisateur <span class="font-bold text-gray-900">"{{ $deleteName }}"</span> ? Toutes ses données seront perdues.
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
