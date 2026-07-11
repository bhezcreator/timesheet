<div class="py-0">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Héritage du Projet : <span class="text-blue-600 font-bold">{{ $project->name }}</span>
            </h2>
            <p class="text-xs text-gray-500 font-mono">Code du Projet parent : {{ $project->code }}</p>
        </div>
    </x-slot>

    <x-ui.breadcrumb :items="[
        [
            'label' => 'Tableau de bord',
            'url'   => route('dashboard')
        ],
        [
            'label' => 'Projets',
            'url'   => route('projects.index')
        ],
        [
            'label' => 'Gestion des sous-projets'
        ]
    ]" />

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

        <!-- Liste récapitulative des alertes de saisie -->
        @if($errors->any())
            <x-ui.alert type="error" class="mb-4 mt-8">
                <div class="flex flex-col gap-1">
                    <span class="font-bold text-sm mb-1">Veuillez ajuster les données du sous-projet :</span>
                    <ul class="list-disc list-inside text-xs space-y-0.5 opacity-90">
                        @foreach ($errors->all() as $error)
                            @if($error !== $errors->first('permission'))
                                <li>{{ $error }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </x-ui.alert>
            <br>
        @endif

        <!-- Tableau principal ou État vide -->
        @if($subProjects->count())
            <x-ui.table :columns="['N°', 'Intitulé du Sous-Projet', 'Équipe affectée', 'Statut', 'Actions']">
                <x-slot:header>
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-2xl font-bold text-gray-900">Sous-projets configurés</h1>
                        <x-ui.button wire:click="openModal">
                            <i class="las la-plus mr-1"></i> Nouveau sous-projet
                        </x-ui.button>
                    </div>
                    <x-ui.forms.input wire:model.live.debounce.300ms="search" placeholder="Filtrer les sous-projets par nom..." />
                </x-slot:header>

                <tbody>
                    @foreach($subProjects as $sub)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="subproject-row-{{ $sub->id }}">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-400">
                                {{ ($subProjects->currentPage() - 1) * $subProjects->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                {{ $sub->name }}
                            </td>
                            <td class="px-6 py-4 text-sm max-w-md">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($sub->users as $u)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium border border-gray-200">
                                            <i class="las la-user mr-1 text-gray-400"></i>{{ $u->first_name }} {{ $u->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">Aucun membre assigné</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($sub->status === 'complete')
                                    <x-ui.badge variant="success">Complété</x-ui.badge>
                                @elseif($sub->status === 'active')
                                    <x-ui.badge variant="info">Actif</x-ui.badge>
                                @elseif($sub->status === 'annuler')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Annulé</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Brouillon</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm space-x-1 whitespace-nowrap text-right">
                                <x-ui.button variant="outline" wire:click="edit({{ $sub->id }})" title="Modifier le sous-projet">
                                    <i class="las la-edit"></i>
                                </x-ui.button>
                                <x-ui.button variant="danger" wire:click="confirmDelete({{ $sub->id }})" title="Supprimer le sous-projet">
                                    <i class="las la-trash"></i>
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <div class="mt-5">
                <x-ui.pagination :paginator="$subProjects" />
            </div>
        @else
            <x-ui.empty-state title="Aucun sous-projet" description="Divisez ce projet principal en plusieurs lots technologiques ou opérationnels." icon="las la-folder">
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Créer le premier sous-projet
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @endif
    </div>

    <!-- FENÊTRE MODALE : Saisie de données du Sous-Projet -->
    <x-ui.modal-one id="sub-project-modal" title="{{ $subProjectId ? 'Configuration du sous-projet' : 'Créer un sous-projet' }}" size="xl">
        <div class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <x-ui.forms.input label="Intitulé du Sous-Projet" name="name" wire:model="name" placeholder="Ex: Développement Backend de l'API" required />
                </div>
                <x-ui.forms.select
                    name="status"
                    label="Statut Opérationnel"
                    :selected="$status"
                    required
                    :options="[
                        ['value' => 'brouillon', 'label' => 'Brouillon', 'icon' => 'las la-edit', 'description' => 'En cours de réflexion'],
                        ['value' => 'active', 'label' => 'Actif', 'icon' => 'las la-play-circle', 'description' => 'Lot en cours de production'],
                        ['value' => 'annuler', 'label' => 'Annulé', 'icon' => 'las la-ban', 'description' => 'Lot suspendu définitivement'],
                        ['value' => 'complete', 'label' => 'Complété', 'icon' => 'las la-check-circle', 'description' => 'Livrables validés et clos']
                    ]"
                />
            </div>

            <!-- Description Auto-ajustable -->
            <x-ui.forms.textarea
                name="description"
                label="Description &amp; Périmètre"
                wire:model="description"
                rows="3"
                placeholder="Spécifiez les objectifs ou les jalons de ce sous-projet..."
                helper="S'ouvre et s'ajuste dynamiquement en fonction du volume d'informations écrit."
                maxlength="1000"
            />

            <!-- GRILLE DE LIAISON : Affectation des collaborateurs au sous-projet (Table sub_project_user) -->
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-1 select-none">
                    <i class="las la-users text-lg text-gray-500"></i> Équipe assignée à ce sous-projet (Table de liaison)
                </h4>
                @if($allUsers->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-40 overflow-y-auto p-1 bg-gray-50 rounded-xl border border-gray-200/50">
                        @foreach($allUsers as $user)
                            <label class="relative flex items-start p-3 rounded-lg border border-gray-200 bg-white hover:bg-blue-50/30 transition cursor-pointer select-none" wire:key="user-choice-{{ $user->id }}">
                                <div class="flex h-5 items-center">
                                    <input type="checkbox" value="{{ $user->id }}" wire:model="assignedUsers" id="user-cb-{{ $user->id }}" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                </div>
                                <div class="ml-3 text-xs">
                                    <span class="font-semibold text-gray-700 block">{{ $user->first_name }} {{ $user->name }}</span>
                                    <span class="text-gray-400 font-normal">{{ $user->job_title }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-yellow-600 bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                        ⚠️ Aucun agent actif n'est configuré dans le système pour composer l'équipe.
                    </p>
                @endif
                <x-ui.forms.error name="assignedUsers" />
            </div>
        </div>

        <!-- Boutons de validation de la modale -->
        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <x-ui.button variant="outline" wire:click="closeModal" data-close-modal>
                    Annuler
                </x-ui.button>
                <x-ui.button wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="las la-save mr-1"></i> Enregistrer le sous-projet</span>
                    <span wire:loading><i class="las la-spinner la-spin mr-1"></i> Traitement...</span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal-one>

    <!-- FENÊTRE MODALE : Confirmation de suppression -->
    <x-ui.modal-one id="delete-sub-project-modal" title="Confirmation de suppression" size="sm">
        <div class="text-center py-2">
            <i class="las la-exclamation-triangle text-red-500 text-5xl block mb-3 animate-pulse"></i>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Supprimer le sous-projet ?</h3>
            <p class="text-sm text-gray-500 px-2">
                Voulez-vous supprimer définitivement la fiche <span class="font-bold text-gray-900">"{{ $deleteName }}"</span> ? Les relations d'équipe de ce lot technique seront rompues.
            </p>
        </div>
        <x-slot:footer>
            <div class="flex justify-center w-full gap-3">
                <x-ui.button variant="outline" data-close-modal>
                    Annuler
                </x-ui.button>
                <x-ui.button variant="danger" wire:click="delete({{ $deleteId ?? 0 }})" wire:loading.attr="disabled">
                    Confirmer
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal-one>
</div>
