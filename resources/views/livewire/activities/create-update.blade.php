<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $isEditMode ? 'Modifier cette activité' : 'Déclarer une activité' }}
        </h2>
    </x-slot>

    <div class="flex items-center mb-4">
        <a href="{{ route('activities.index') }}"
            wire:navigate class="font-semibold text-gray-500 inline-flex items-center gap-2 text-sm hover:text-indigo-600 transition-colors">
            <i class="las la-arrow-left text-base"></i> Retour
        </a>
    </div>

    <div class="space-y-4 bg-white p-4 md:p-6 rounded-xl border border-gray-200 shadow-sm">
        <h2 class="font-semibold text-xl text-gray-700 leading-tight">
            {{ $isEditMode ? 'Modifier cette activité' : 'Ajouter une nouvelle activité' }}
        </h2>

        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
            <i class="las la-info-circle text-blue-500 text-sm"></i>
            Total de jours ouvrés théoriques pour le mois de
            <strong class="text-gray-700">{{ $monthLabel }}</strong> :
            <span class="font-semibold text-blue-600">
                <span class="font-black">{{ $userLoggedDaysCount }}</span>/{{ $workingDaysCount }} jours complétés
            </span>.
        </p>
    </div>

    <div class="w-full mt-4">
        <!-- Alertes de session -->
        @if(session('success'))
            <x-ui.alert type="success" class="mb-4">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        <!-- Message d'erreur exclusif sur droit ou blocage structurel -->
        @error('permission')
            <x-ui.alert type="error" class="mb-6">{{ $message }}</x-ui.alert> <br>
        @enderror

        @error('activity')
            <x-ui.alert type="error" class="mb-6">{{ $message }}</x-ui.alert>
            <br>
        @enderror

        <!-- Corps du formulaire moderne -->
        <form wire:submit.prevent="save" class="space-y-4 bg-white p-4 md:p-6 rounded-xl border border-gray-200 shadow-sm">

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
                    wire:model="description"
                    required
                    label="Description / Livrables produits"
                    helper="Décrivez les tâches réalisées."
                    placeholder="Décrivez brièvement les tâches accomplies (Obligatoire selon les paramètres de l'organisation)..."
                />
            </div>

            <!-- Section 5 : COMPOSANT EN TEMPS RÉEL : Affiché uniquement si un projet est sélectionné -->
            @if($project_id && $projectProgressPercentage > 0)
                <div class="space-y-2 bg-gray-50 border border-gray-200/60 rounded-xl p-4 mt-2 transition-all duration-300">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Votre taux d'imputation mensuel : <strong class="text-gray-800 font-medium">{{ $currentProjectName }}</strong>
                        </span>
                        <span class="text-sm font-mono font-black text-blue-600">
                            {{ $projectProgressPercentage }}%
                        </span>
                    </div>

                    <!-- Votre composant d'UI réutilisable -->
                    <x-ui.progress-bar
                        value="{{ $projectProgressPercentage }}"
                    />
                    <p class="text-[10px] text-gray-400">Ce pourcentage représente la part de vos heures déclarées sur ce projet par rapport au volume mensuel attendu.</p>
                </div>
            @endif
            <!-- Pied de page / Actions de validation -->
            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6 mt-6">
                <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                    Annuler
                </a>

                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="save">
                    <!-- Visible uniquement si la méthode save n'est pas en cours d'exécution -->
                    <span wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                        <i class="las la-check-circle text-lg"></i> {{ $isEditMode ? 'Enregistrer les modifications' : 'Ajouter au brouillon' }}
                    </span>

                    <!-- Visible UNIQUEMENT pendant l'exécution de la méthode save -->
                    <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                        <i class="las la-spinner animate-spin text-lg"></i> Traitement de la ligne...
                    </span>
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
