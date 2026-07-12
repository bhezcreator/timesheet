<div class="py-0">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <span class="text-blue-600 font-bold">{{ $project->name }}</span>
            </h2>
            <p class="text-xs text-gray-500 font-mono">Code : {{ $project->code }}</p>
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
        @if(!$subProjects->count() And empty($search))
            <x-ui.empty-state title="Aucun sous-projet" description="Divisez ce projet principal en plusieurs lots technologiques ou opérationnels." icon="las la-folder">
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Créer le premier sous-projet
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <div>
                {{-- Zone En-tête : Titre, Action d'ajout et Barre de recherche --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Sous-projets configurés</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Suivi en direct des lots et des équipes assignées.</p>
                    </div>
                    <x-ui.button wire:click="openModal" class="shrink-0 shadow-sm">
                        <i class="las la-plus mr-1.5 text-base"></i> Nouveau sous-projet
                    </x-ui.button>
                </div>

                <div class="mb-6">
                    <x-ui.forms.input wire:model.live.debounce.300ms="search" placeholder="Filtrer les sous-projets par nom..." />
                </div>

                {{-- Grille responsive de cartes --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($subProjects as $sub)
                        <div class="flex flex-col justify-between bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-gray-200/80 transition-all duration-200 p-5" wire:key="subproject-card-{{ $sub->id }}">

                            {{-- Partie haute de la carte --}}
                            <div class="space-y-4">
                                {{-- Index discret et Statut --}}
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-300 bg-gray-50 px-2 py-1 rounded-lg">
                                        N° {{ ($subProjects->currentPage() - 1) * $subProjects->perPage() + $loop->iteration }}
                                    </span>

                                    <div>
                                        @if($sub->status === 'actif')
                                            <x-ui.badge variant="success">Actif</x-ui.badge>
                                        @elseif($sub->status === 'annuler')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100">Annulé</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-50 text-gray-600 border border-gray-100">Brouillon</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Titre du sous-projet --}}
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 tracking-tight line-clamp-2 min-h-[3rem]" title="{{ $sub->name }}">
                                        {{ $sub->name }}
                                    </h3>
                                </div>

                                {{-- Description du sous-projet --}}
                                <div class="mt-2 text-sm text-gray-500 line-clamp-2 leading-relaxed min-h-[2.5rem]" title="{{ $sub->description }}">
                                    {{ $sub->description ?? 'Aucune description disponible pour ce sous-projet.' }}
                                </div>

                                {{-- Zone Équipe affectée --}}
                                <div class="pt-3 border-t border-gray-50">
                                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Équipe affectée</span>
                                    <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto pr-1">
                                        @forelse($sub->users as $u)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-gray-50 border border-gray-200/60 text-gray-700 text-xs font-medium transition-colors hover:bg-gray-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                                {{ $u->first_name }} {{ $u->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400 italic flex items-center gap-1">
                                                <i class="las la-user-slash text-sm"></i> Aucun membre assigné
                                            </span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            {{-- Partie basse : Boutons d'actions --}}
                            <div class="mt-5 pt-4 border-t border-gray-50 flex items-center justify-end gap-2">
                                <x-ui.button variant="outline" size="sm" wire:click="edit({{ $sub->id }})" title="Modifier le sous-projet" class="!rounded-xl flex-1 justify-center md:flex-none">
                                    <i class="las la-edit text-base"></i> <span class="md:hidden ml-1">Modifier</span>
                                </x-ui.button>

                                <x-ui.button variant="danger" size="sm" wire:click="confirmDelete({{ $sub->id }})" title="Supprimer le sous-projet" class="!rounded-xl flex-1 justify-center md:flex-none">
                                    <i class="las la-trash text-base"></i> <span class="md:hidden ml-1">Supprimer</span>
                                </x-ui.button>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-5">
                <x-ui.pagination :paginator="$subProjects" />
            </div>
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
                    wire:model="status"
                    label="Statut Opérationnel"
                    :selected="$status"
                    required
                    :options="[
                        ['value' => 'brouillon', 'label' => 'Brouillon', 'icon' => 'las la-edit', 'description' => 'En cours de réflexion'],
                        ['value' => 'actif', 'label' => 'Actif', 'icon' => 'las la-play-circle', 'description' => 'Lot en cours de production'],
                        ['value' => 'annuler', 'label' => 'Annulé', 'icon' => 'las la-ban', 'description' => 'Lot suspendu définitivement'],
                    ]"
                />
            </div>

            <!-- Description Auto-ajustable -->
            <x-ui.forms.textarea
                name="description"
                label="Description"
                wire:model="description"
                rows="3"
                placeholder="Spécifiez les objectifs ou les jalons de ce sous-projet..."
                helper="S'ouvre et s'ajuste dynamiquement en fonction du volume d'informations écrit."
                maxlength="1000"
            />
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
                Voulez-vous supprimer définitivement ce sous-projet <span class="font-bold text-gray-900">"{{ $deleteName }}"</span> ?
                <br><br>
                Les relations d'équipe de ce lot technique seront rompues.
            </p>
        </div>
        <x-slot:footer>
            <div class="flex justify-center w-full gap-3">
                <x-ui.button variant="outline" data-close-modal>
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
        </x-slot:footer>
    </x-ui.modal-one>
</div>
