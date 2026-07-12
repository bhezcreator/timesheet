<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestion des projets
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
{{--         @error('permission')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert>
        @enderror --}}

        <!-- Liste récapitulative des alertes de saisie -->
        @if($errors->any())
            <x-ui.alert type="error" class="mb-4 mt-8">
                <div class="flex flex-col gap-1">
                    <span class="font-bold text-sm mb-1">Message(s) :</span>
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
        @if(!$projects->count() And empty($search))
            <x-ui.empty-state title="Aucun projet trouvé" description="Créez et configurez vos projets d'entreprise." icon="las la-folder-open">
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Créer un projet
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <div>
                {{-- Section En-tête : Titre, Bouton d'ajout et Barre de Recherche globale --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Liste des projets</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Gérez vos projets principaux et leurs lots de sous-projets.</p>
                    </div>
                    <x-ui.button wire:click="openModal" class="shrink-0 shadow-sm">
                        <i class="las la-plus mr-1.5 text-base"></i> Nouveau projet
                    </x-ui.button>
                </div>

                <div class="mb-6">
                    <x-ui.forms.input wire:model.live.debounce.300ms="search" placeholder="Recherche par code, nom ou statut..." />
                </div>

                {{-- Grille principale des cartes de projets --}}
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    @foreach($projects as $project)
                        <div class="flex flex-col bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200" wire:key="project-card-{{ $project->id }}">

                            {{-- 1. EN HAUT : Informations principales du Projet --}}
                            <div class="p-6 border-b border-gray-50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-mono font-semibold bg-gray-100 px-2 py-0.5 rounded text-xs text-gray-600 tracking-wider">
                                                {{ $project->code }}
                                            </span>
                                            @if($project->status === 'complete')
                                                <x-ui.badge variant="success">Fini</x-ui.badge>
                                            @elseif($project->status === 'active')
                                                <x-ui.badge variant="info">Actif</x-ui.badge>
                                            @elseif($project->status === 'annuler')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100">Annuler</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-50 text-gray-600 border border-gray-100">Brouillon</span>
                                            @endif
                                        </div>
                                        <h2 class="text-lg font-bold text-gray-900 pt-1 line-clamp-1">
                                            {{ $project->name }}
                                        </h2>
                                    </div>

                                    {{-- Numéro d'index discret --}}
                                    <span class="text-xs font-bold text-gray-300 bg-gray-50 h-7 w-7 rounded-full flex items-center justify-center shrink-0">
                                        {{ ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration }}
                                    </span>
                                </div>

                                {{-- Métadonnées (Manager & Dates) --}}
                                <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-dashed border-gray-100 text-xs">
                                    <div class="space-y-1">
                                        <span class="text-gray-400 block font-medium uppercase tracking-wider">Manager</span>
                                        <span class="font-semibold text-gray-800 flex items-center gap-1">
                                            <i class="las la-user text-sm text-gray-400"></i>
                                            {{ $project->manager ? $project->manager->first_name . ' ' . $project->manager->name : 'Non assigné' }}
                                        </span>
                                    </div>
                                    <div class="space-y-1">
                                        <span class="text-gray-400 block font-medium uppercase tracking-wider">Période</span>
                                        <span class="font-medium text-gray-700 flex items-center gap-1">
                                            <i class="las la-calendar text-sm text-gray-400"></i>
                                            {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') : '??' }}
                                            →
                                            {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') : '??' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Description du projet --}}
                            <div class="px-6 bg-white border-b border-gray-50 text-sm text-gray-500 line-clamp-2 leading-relaxed min-h-[2.5rem]" title="{{ $project->description }}">
                                {{ $project->description ?? 'Aucune description disponible pour ce sous-projet.' }}
                            </div>

                            {{-- 2. EN BAS : Liste des Sous-Projets (Lots) liés --}}
                            <div class="flex-1 p-6 bg-gray-50/50">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="las la-cubes text-sm"></i> Sous-projets associés
                                    </h3>
                                    <span class="bg-gray-200 text-gray-700 text-xs font-bold px-2 py-0.5 rounded-full">
                                        {{ $project->subProjects->count() }}
                                    </span>
                                </div>

                                @if($project->subProjects->isNotEmpty())
                                    <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                                        @foreach($project->subProjects as $subProject)
                                            <div class="flex items-center justify-between p-2.5 bg-white border border-gray-100 rounded-xl shadow-xs">
                                                <div class="min-w-0 flex-1 pr-2">
                                                    <h4 class="text-sm font-semibold text-gray-800 truncate">{{ $subProject->name }}</h4>
                                                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ $subProject->description }}</p>
                                                </div>
                                                <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded-md {{ $subProject->status === 'complete' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                                    {{ $subProject->status }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center py-6 border border-dashed border-gray-200 rounded-xl bg-white/50">
                                        <i class="las la-folder-open text-gray-300 text-2xl mb-1"></i>
                                        <p class="text-xs text-gray-400 font-medium">Aucun sous-projet pour le moment</p>
                                    </div>
                                @endif
                            </div>

                            {{-- 3. PARTIE : Nombre des personnels Attribués --}}
                            <div class="flex-1 p-4 bg-blue-50/50">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="las la-users text-sm"></i> Personnels attribués
                                    </h3>
                                    <span class="bg-gray-200 text-gray-700 text-xs font-bold px-2 py-0.5 rounded-full">
                                        {{ $project->users->count() }}
                                    </span>
                                </div>
                            </div>

                            {{-- 4. PIED DE PAGE : Boutons d'actions du Projet --}}
                            <div class="p-4 bg-white border-t border-gray-50 rounded-b-2xl flex items-center justify-end gap-2">
                                <a
                                    href="{{ route('projects.subprojects', ['projectId' => $project->id]) }}"
                                    wire:navigate
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium rounded-xl border border-gray-200 bg-green text-gray-700 hover:text-blue-600 hover:bg-blue-50/50 hover:border-blue-100 transition shadow-xs mr-auto"
                                    title="Gérer les sous-projets">
                                    <i class="las la-folder-plus text-base"></i>
                                    <span>Sous-projet</span>
                                </a>

                                <x-ui.button variant="outline" size="sm" wire:click="edit({{ $project->id }})" title="Éditer le projet" class="!rounded-xl">
                                    <i class="las la-edit text-base"></i>
                                </x-ui.button>

                                <x-ui.button variant="danger" size="sm" wire:click="confirmDelete({{ $project->id }})" title="Supprimer le projet" class="!rounded-xl">
                                    <i class="las la-trash text-base"></i>
                                </x-ui.button>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-5">
                <x-ui.pagination :paginator="$projects" />
            </div>
        @endif
    </div>

    <!-- FENÊTRE MODALE : Saisie de données du Projet -->
    <x-ui.modal-one id="project-modal" title="{{ $projectId ? 'Mise à jour du projet' : 'Créer un nouveau projet' }}" size="xl">
        <div class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-ui.forms.input label="Code du Projet" name="code" wire:model="code" placeholder="Ex: PRJ-001" required />
                <div class="md:col-span-2">
                    <x-ui.forms.input label="Intitulé du Projet" name="name" wire:model="name" placeholder="Ex: Migration Cloud ERP" required />
                </div>
            </div>

            <div class="w-full">
                <x-ui.forms.textarea
                wire:model="description"
                    name="description"
                    label="Description / Objectifs"
                    wire:model="description"
                    rows="3"
                    placeholder="Saisir la description ou le résumé du projet..."
                    helper="Décrivez les activités réalisées."
                />
                <x-ui.forms.error name="description" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Intégration du composant Select personnalisé pour le Manager -->
                <x-ui.forms.select
                    wire:model="manager_id"
                    name="manager_id"
                    label="Manager du Projet"
                    placeholder="Choisir le gestionnaire responsable..."
                    :selected="$manager_id"
                    required
                    :options="$managers->map(fn($m) => [
                        'value'       => (string)$m->id,
                        'label'       => $m->first_name . ' ' . $m->name,
                        'description' => $m->job_title,
                        'icon'        => 'las la-user-tie'
                    ])->toArray()"
                />

                <!-- Intégration du composant Select personnalisé pour le Statut -->
                <x-ui.forms.select
                    wire:model="status"
                    name="status"
                    label="Statut Initial"
                    :selected="$status"
                    required
                    :options="[
                            [
                                'value'       => 'brouillon',
                                'label'       => 'Brouillon',
                                'icon'        => 'las la-edit', {{-- Icône de stylo/édition pour un brouillon --}}
                                'description' => 'En cours d\'écriture'
                            ],
                            [
                                'value'       => 'active',
                                'label'       => 'Actif',
                                'icon'        => 'las la-play-circle', {{-- Icône de lecture/lancement pour un projet actif --}}
                                'description' => 'Projet actif et en cours d\'exécution'
                            ],
                            [
                                'value'       => 'annuler',
                                'label'       => 'Annulé',
                                'icon'        => 'las la-ban', {{-- Icône de restriction/interdiction claire pour une annulation --}}
                                'description' => 'Projet stoppé ou abandonné'
                            ],
                            [
                                'value'       => 'complete',
                                'label'       => 'Complété',
                                'icon'        => 'las la-check-circle', {{-- Icône de succès verte de coche pour la complétion --}}
                                'description' => 'Projet clôturé avec succès'
                            ]
                        ]"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Champ Date de Début -->
                <x-ui.forms.datepicker
                    wire:model="start_date"
                    name="start_date"
                    label="Date de Début"
                    :selected="$start_date"
                    placeholder="Sélectionner la date de début..."
                    required
                />

                <!-- Champ Date de Fin Prévisionnelle -->
                <x-ui.forms.datepicker
                wire:model="end_date"
                    name="end_date"
                    label="Date de Fin Prévisionnelle"
                    :selected="$end_date"
                    placeholder="Sélectionner la date de fin..."
                    required
                />
            </div>
        </div>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <x-ui.button variant="outline" wire:click="closeModal" data-close-modal>Annuler</x-ui.button>
                <x-ui.button wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="las la-save mr-1"></i> Enregistrer le projet</span>
                    <span wire:loading><i class="las la-spinner la-spin mr-1"></i> Traitement...</span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal-one>

    <!-- FENÊTRE MODALE : Confirmation de suppression -->
    <x-ui.modal-one id="delete-project-modal" title="Confirmation de suppression" size="sm">
        <div class="text-center py-2">
            <i class="las la-exclamation-triangle text-red-500 text-5xl block mb-3 animate-pulse"></i>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Supprimer le projet ?</h3>
            <p class="text-sm text-gray-500 px-2">
                Voulez-vous supprimer définitivement la fiche du projet <span class="font-bold text-gray-900">"{{ $deleteName }}"</span> ? Les liaisons avec les activités et sous-projets associés seront rompues.
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
