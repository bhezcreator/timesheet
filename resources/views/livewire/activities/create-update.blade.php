<div class="py-0">
     <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Attribution des projets
        </h2>
    </x-slot>

    <div class="flex items-center mb-4">
        <a href="{{ url()->previous() }}"
            wire:navigate class="font-semibold text-gray-500 inline-flex items-center gap-2 text-sm hover:text-indigo-600 transition-colors">
            <i class="las la-arrow-left text-base"></i> Retour
        </a>
    </div>

    <div class="space-y-4 bg-white p-4 md:p-6 rounded-xl border border-gray-200 shadow-sm">
        <h2 class="font-semibold text-xl text-gray-700 leading-tight">
            {{ $isEditMode ? 'Modifier cette activité' : 'Ajouter une nouvelle activité' }}
        </h2>

        <p class="text-xs text-gray-500 mt-1">
            <i class="las la-info-circle text-blue-500"></i> Total de jours ouvrés théoriques pour le mois de <strong class="text-gray-700">{{ $monthLabel }}</strong> : <span class="font-bold text-blue-600">{{ $workingDaysCount }} jours</span>.
        </p>
    </div>

    <div class="w-full mt-4">
        <!-- Message d'erreur exclusif sur droit ou blocage structurel -->
        @error('permission')
            <x-ui.alert type="error" class="mb-6">{{ $message }}</x-ui.alert> <br>
        @enderror
        @error('activity')
            <x-ui.alert type="error" class="mb-6">{{ $message }}</x-ui.alert>
            <br>
        @enderror

        <!-- Corps du formulaire moderne -->
        <form wire:submit.prevent="save" class="space-y-6 bg-white p-6 md:p-8 rounded-xl border border-gray-200 shadow-sm pb-10">

            <!-- Section 1 : Informations Générales -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2 uppercase tracking-wider opacity-60">
                    <i class="las la-info-circle text-base"></i> Détails de la mission
                </h3>

                <x-ui.forms.input
                    label="Titre explicite de l'activité"
                    name="titre"
                    required
                    wire:model.live="titre"
                    placeholder="Ex: Rédaction du rapport de projet, Distribution de kits de secours..."
                />
                <x-ui.forms.error name="titre" />
            </div>

            <!-- Section 2 : Allocations & Catégories -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-100 pt-5">
                <!-- Projet -->
                <div>
                    <x-ui.forms.searchable-select
                        wire:model="project_id"
                        name="project_id"
                        label="Projet parent"
                        placeholder="-- Sélectionner un projet parent --"
                        :selected="$project_id"
                        :live="true"
                        required
                        :options="$projects->map(fn($project) => [
                            'value' => $project->id,
                            'label' => $project->name,
                            'code'  => $project->code
                        ])->toArray()"
                    />
                </div>

                @php
                    $count = count($subProjects);
                @endphp
                    <!-- On enveloppe dans une condition pour ne l'afficher que si un projet est choisi -->
                @if($project_id And $count !== 0)
                    <x-ui.forms.searchable-select
                        wire:key="sub-projects-list-for-project-{{ $project_id }}-count-{{ count($subProjects) }}"
                        wire:model="sub_project_id"
                        name="sub_project_id"
                        label="Sous-projet associé"
                        placeholder="-- Sélectionner un sous-projet --"
                        icon="las la-sitemap"
                        required
                        :selected="$sub_project_id"
                        :options="$subProjects->map(fn($sub) => [
                            'value'       => (string)$sub->id,
                            'label'       => $sub->name,
                            'description' => 'Composant du projet parent'
                        ])->toArray()"
                    />
                @else
                    <!-- État d'attente grisé si aucun projet n'est sélectionné -->
                    <div class="opacity-50 pointer-events-none pt-1">
                        <label class="block text-xs font-bold tracking-wider text-gray-400 mb-2">Sous-projet associé</label>
                        <div class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm text-gray-400 select-none">
                            @if ($count !== 0)
                                <span>-- Sélectionnez d'abord un projet --</span>
                            @else
                                <span>-- Aucun sous projet --</span>
                            @endif
                            <i class="las la-angle-down text-xs"></i>
                        </div>
                    </div>
                @endif

                <!-- Type d'activité -->
                <div>
                    <x-ui.forms.select
                        wire:model="activity_type_id"
                        required
                        name="activity_type_id"
                        label="Type d'imputation"
                        placeholder="-- Sélectionner un type --"
                        :selected="$activity_type_id"
                        :options="$activityTypes->map(fn($type) => [
                            'value'       => (string)$type->id,
                            'label'       => $type->name,
                            'description' => $type->description ? Str::limit($type->description, 60) : 'Option système',
                            'icon'        => 'las la-tag shadow-xs'
                        ])->toArray()" />
                    <x-ui.forms.error name="activity_type_id" />
                </div>
            </div>

            <!-- Section 3 : Horodatage -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-gray-100 pt-5">
                <div>
                    <x-ui.forms.input type="date" required label="Date de réalisation" name="activity_date" wire:model="activity_date" />
                    <x-ui.forms.error name="activity_date" />
                </div>
                <div>
                    <x-ui.forms.input type="time" required label="Heure de début" name="start_time" wire:model="start_time" />
                    <x-ui.forms.error name="start_time" />
                </div>
                <div>
                    <x-ui.forms.input required type="time" label="Heure de fin" name="end_time" wire:model="end_time" />
                    <x-ui.forms.error name="end_time" />
                </div>
            </div>

            <!-- Section 4 : Commentaires / Description -->
            <div class="border-t border-gray-100 pt-5">
                <x-ui.forms.textarea
                    name="description"
                    required
                    label="Description / Livrables produits"
                    helper="Décrivez les activités réalisées."
                    placeholder="Décrivez brièvement les tâches accomplies (Obligatoire selon les paramètres de l'organisation)..."
                />
                <x-ui.forms.error name="description" />
            </div>

            <!-- Pied de page / Actions de validation -->
            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6 mt-6">
                <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                    Annuler
                </a>
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove class="flex items-center gap-1.5">
                        <i class="las la-check-circle text-lg"></i> {{ $isEditMode ? 'Enregistrer les modifications' : 'Ajouter au brouillon' }}
                    </span>
                    <span wire:loading class="flex items-center gap-1.5">
                        <i class="las la-spinner animate-spin text-lg"></i> Traitement de la ligne...
                    </span>
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
