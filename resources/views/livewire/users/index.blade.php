<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestion du personnel
        </h2>
    </x-slot>

    <div class="w-full">
        <!-- Messages flash de succès -->
        @if(session('success'))
            <x-ui.alert type="success" class="mb-4 mt-8">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <!-- Traitement exclusif des erreurs d'autorisation -->
        @error('permission')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert>
        @enderror

        @error('user')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert> <br>
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
            <x-ui.table :columns="['N°', 'Matricule', 'Collaborateur', 'Poste / Fonction', 'Superviseur', 'Rôles d\'accès', 'Statut', 'Actions']">
                <x-slot:header>
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-2xl font-bold text-gray-900">Personnel</h1>
                        <x-ui.button wire:click="openModal">
                            <i class="las la-plus mr-1"></i> Ajouter un personnel
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

    <!-- FENÊTRE MODALE : Saisie de données de l'Agent -->
    <x-ui.modal-one id="user-modal" title="{{ $userId ? 'Mise à jour du profil collaborateur' : 'Enregistrer un nouveau collaborateur' }}" size="xl">
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

        <div class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.forms.input label="Numéro d'Ordre / Matricule" name="num_order" wire:model="num_order" required placeholder="Ex: MAT-2026-001" />
                <x-ui.forms.input label="Adresse Email Professionnelle" name="email" required type="email" wire:model="email" placeholder="nom.prenom@entreprise.com" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-ui.forms.input label="Prénom" name="first_name" wire:model="first_name" required placeholder="Ex: Jean" />
                <x-ui.forms.input label="Nom" name="name" wire:model="name" required placeholder="Ex: MOKIA" />
                <x-ui.forms.input label="Postnom" name="last_name" required wire:model="last_name" placeholder="FULA" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.forms.input label="Intitulé du Poste / Fonction" name="job_title" required wire:model="job_title" placeholder="Ex: Chef de Projet Informatique" />

                <div class="space-y-2">
                    <x-ui.forms.select wire:model="supervisor_id"
                        name="supervisor_id"
                        label="Superviseur Direct"
                        placeholder="-- Aucun (Supérieur Hiérarchique direct) --"
                        :selected="$supervisor_id"
                        :options="$supervisors->map(fn($sup) => [
                            'value'       => (string)$sup->id,
                            'label'       => $sup->first_name . ' ' . $sup->name,
                            'description' => $sup->job_title,
                            'icon'        => 'las la-user-tie'
                        ])->toArray()"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <x-ui.forms.input label="Mot de passe {{ $userId ? '(Laisser vide pour ne pas modifier)' : '' }}" :required="!$userId" name="password" type="password" wire:model="password" placeholder="Minimum 8 caractères" />

                <div class="space-y-2 pt-6">
                    <label class="relative flex items-center p-3 rounded-xl border border-gray-200 bg-gray-50 cursor-pointer select-none">
                        <input type="checkbox" wire:model="is_active" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <span class="ml-3 text-sm font-semibold text-gray-700">Compte actif (Autoriser l'accès au système)</span>
                    </label>
                </div>
            </div>

            <!-- Grille d'affectation des rôles de sécurité Spatie -->
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-1">
                    <i class="las la-user-shield text-lg text-gray-500"></i> Attribution des rôles applicatifs
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-40 overflow-y-auto p-1 bg-gray-50 rounded-xl border border-gray-200/50">
                    @foreach($allRoles as $role)
                        <label class="relative flex items-start p-3 rounded-lg border border-gray-200 bg-white hover:bg-blue-50/30 transition cursor-pointer select-none" wire:key="role-choice-{{ $role->id }}">
                            <div class="flex h-5 items-center">
                                <input type="checkbox" value="{{ $role->id }}" wire:model="selectedRoles" id="role-cb-{{ $role->id }}" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </div>
                            <div class="ml-3 text-xs">
                                <span class="font-semibold text-gray-700 block">{{ $role->name }}</span>
                                <span class="text-gray-400 font-normal">Garde: {{ $role->guard_name }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                <x-ui.forms.error name="selectedRoles" />
            </div>
        </div>

        <!-- Boutons de validation de la modale -->
        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <x-ui.button variant="outline" wire:click="closeModal" data-close-modal>
                    Annuler
                </x-ui.button>
                <x-ui.button wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="las la-save mr-1"></i> Enregistrer l'agent</span>
                    <span wire:loading><i class="las la-spinner la-spin mr-1"></i> Traitement...</span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal-o>

    <!-- Modale de Confirmation de suppression -->
    <x-ui.modal-one id="delete-user-modal" title="Confirmation de suppression" size="sm">
        <div class="text-center py-2">
            <i class="las la-exclamation-triangle text-red-500 text-5xl block mb-3 animate-pulse"></i>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Supprimer le compte ?</h3>
            <p class="text-sm text-gray-500 px-2">
                Voulez-vous supprimer définitivement la fiche de <span class="font-bold text-gray-900">"{{ $deleteName }}"</span> ? Cette opération rompra ses liens de supervision.
            </p>
        </div>
        <x-slot:footer>
            <div class="flex justify-center w-full gap-3">
                <x-ui.button variant="outline" data-close-modal>Annuler</x-ui.button>
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
        </x-slot:footer>
    </x-ui.modal-o>
</div>
