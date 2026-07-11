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
        @error('permission')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert>
        @enderror

        <!-- Liste récapitulative des alertes de saisie -->
        @if($errors->any())
            <x-ui.alert type="error" class="mb-4 mt-8">
                <div class="flex flex-col gap-1">
                    <span class="font-bold text-sm mb-1">Veuillez ajuster les données du formulaire :</span>
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
        @if($projects->count())
            <x-ui.table :columns="['N°', 'Code', 'Intitulé du Projet', 'Manager', 'Date Début', 'Date Fin', 'Statut', 'Actions']">
                <x-slot:header>
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-2xl font-bold text-gray-900">Liste des projets</h1>
                        <x-ui.button wire:click="openModal">
                            <i class="las la-plus mr-1"></i> Nouveau projet
                        </x-ui.button>
                    </div>
                    <x-ui.forms.input wire:model.live.debounce.300ms="search" placeholder="Recherche par code, nom ou statut..." />
                </x-slot:header>

                <tbody>
                    @foreach($projects as $project)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="project-row-{{ $project->id }}">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-400">
                                {{ ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                <span class="font-mono bg-gray-100 px-2 py-1 rounded text-xs text-gray-600">{{ $project->code }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                {{ $project->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $project->manager ? $project->manager->first_name . ' ' . $project->manager->name : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($project->status === 'approved')
                                    <x-ui.badge variant="success">Approuvé</x-ui.badge>
                                @elseif($project->status === 'submitted')
                                    <x-ui.badge variant="info">Soumis</x-ui.badge>
                                @elseif($project->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rejeté</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Brouillon</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm space-x-1 whitespace-nowrap text-right">
                                <x-ui.button variant="outline" wire:click="edit({{ $project->id }})" title="Éditer le projet">
                                    <i class="las la-edit"></i>
                                </x-ui.button>
                                <x-ui.button variant="danger" wire:click="confirmDelete({{ $project->id }})" title="Supprimer le projet">
                                    <i class="las la-trash"></i>
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <div class="mt-5">
                <x-ui.pagination :paginator="$projects" />
            </div>
        @else
            <x-ui.empty-state title="Aucun projet trouvé" description="Créez et configurez vos projets d'entreprise." icon="las la-folder-open">
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Créer un projet
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
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

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Description / Objectifs</label>
                <textarea wire:model="description" rows="3" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition" placeholder="Saisir la description ou le résumé du projet..."></textarea>
                <x-ui.forms.error name="description" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Intégration du composant Select personnalisé pour le Manager -->
                <x-ui.forms.select
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
                    name="status"
                    label="Statut Initial"
                    :selected="$status"
                    required
                    :options="[
                        ['value' => 'draft', 'label' => 'Brouillon', 'icon' => 'las la-file-alt', 'description' => 'En cours d\'écriture'],
                        ['value' => 'submitted', 'label' => 'Soumise', 'icon' => 'las la-paper-plane', 'description' => 'En attente d\'approbation'],
                        ['value' => 'approved', 'label' => 'Approuvée', 'icon' => 'las la-check-circle', 'description' => 'Projet actif et validé'],
                        ['value' => 'rejected', 'label' => 'Rejetée', 'icon' => 'las la-times-circle', 'description' => 'Dossier refusé']
                    ]"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.forms.input label="Date de Début" name="start_date" type="date" wire:model="start_date" required />
                <x-ui.forms.input label="Date de Fin Prévisionnelle" name="end_date" type="date" wire:model="end_date" required />
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
                    Confirmer
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal-one>
</div>
