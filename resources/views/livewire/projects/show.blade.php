<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Description du projet
        </h2>
    </x-slot>

    {{-- Bouton Retour --}}
    <div class="flex items-center justify-between mb-3 p-0">
        <a href="{{ url()->previous() }}"
            wire:navigate class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors font-medium">
            <i class="las la-arrow-left text-base"></i> <strong>Retour</strong>
        </a>

        <div class="flex items-center justify-between gap-4">
            <select
                wire:model.live="moisChoisi"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 cursor-pointer"
            >
                <option value="">Choisir le mois...</option>
                @foreach ($this->mois as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select
                wire:model.live="anneeChoisie"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 cursor-pointer"
            >
                <option value="">Choisir l'année...</option>
                @foreach ($this->annees as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mt-1 mb-1">
                {{ $project->name }}
            </h2>
            <span class="text-xs font-mono uppercase bg-blue-50 text-blue-700 px-2 py-0.5 rounded border border-blue-100">{{ $project->code }}</span> 
            <span class="text-xs font-mono uppercase bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded border border-yellow-100">Début : {{ $project->start_date?->locale('fr')->translatedFormat('d F Y') }}</span>
        </div>

        <div>
            <x-ui.badge variant="warning">
                {{ floor($totalHours) }}h : {{ round(($totalHours - floor($totalHours)) * 60) }}m réalisée(s) 
            </x-ui.badge>

            <x-ui.badge variant="{{ $project->status === 'active' ? 'success' : ($project->status === 'annuler' ? 'warning' : 'danger') }}">
                {{ $project->status === 'active' ? \Illuminate\Support\Str::ucfirst("Actif") : \Illuminate\Support\Str::ucfirst($project->status) }}
            </x-ui.badge>
        </div>
    </div>

    <!-- Écran global du Dashboard -->
    <div class="w-full space-y-6 mt-8 pb-4">

        <!-- GRILLE TOP STATS (Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Card 1 : Avancement Global -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-gray-500">Avancement Global</span>
                    <i class="las la-chart-pie text-2xl text-blue-600"></i>
                </div>
                <div>
                    <span class="text-3xl font-black text-gray-900">{{ $global_progress }}%</span>
                    <div class="w-full bg-gray-100 rounded-full h-2 mt-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: {{ $global_progress }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Card 2 : Sous-Projets -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-gray-500">Sous-projets</span>
                    <i class="las la-folder text-2xl text-amber-500"></i>
                </div>
                <div>
                    <span class="text-3xl font-black text-gray-900">{{ $sub_projects_completed }}
                    <span class="text-lg text-gray-400 font-normal"> / {{ $totalSubProjects }}</span></span>
                    <p class="text-xs text-gray-400 mt-1">Sous-projets marqués comme terminés</p>
                </div>
            </div>

            <!-- Card 3 : Activités -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-gray-500">Activités rattachées</span>
                    <i class="las la-tasks text-2xl text-emerald-500"></i>
                </div>
                <div>
                    <span class="text-3xl font-black text-gray-900">{{ $completedActivities }}<span class="text-lg text-gray-400 font-normal"> 
                    / {{ $totalActivit }}</span></span>
                    <p class="text-xs text-gray-400 mt-1">Actions complétées sur le terrain</p>
                </div>
            </div>

            <!-- Card 4 : Temps Restant -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-gray-500">Échéance</span>
                    <i class="las la-clock text-2xl text-purple-500"></i>
                </div>
                <div>
                    @if($days_remaining === null)
                        <span class="text-xl font-bold text-gray-500">Pas de date</span>
                    @elseif($days_remaining > 0)
                        <span class="text-3xl font-black text-emerald-600">{{ round($days_remaining) }}</span>
                        <span class="text-sm text-gray-500 font-medium">Jours restants</span>
                    @else
                        <span class="text-xl font-bold text-red-600">Échu depuis {{ round(abs($days_remaining)) }} j.</span>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">Fin prévue : {{ $project->end_date ? $project->end_date->format('d/m/Y') : 'Non planifiée' }}</p>
                </div>
            </div>
        </div>

        <!-- Statistiques détaillées -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
            <!-- Activités en brouillon -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Activités en brouillon</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalActivitiesDraft }}</p>
                    </div>
                    <div class="p-3 bg-amber-100 rounded-lg shrink-0" aria-label="Activités en brouillon">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Activités soumises -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Activités soumises</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalActivitiesSubmitted }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg shrink-0" aria-label="Activités soumises">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Activités approuvées -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Activités approuvées</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalActivitiesApproved }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg shrink-0" aria-label="Activités approuvées">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Activités rejetées -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Activités rejetées</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalActivitiesRejected }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-lg shrink-0" aria-label="Activités rejetées">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- ZONE DU BAS : 2 COLONNES (Détails à gauche, Équipe à droite) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Colonne gauche (2/3 de l'espace) : Description & Listes -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-2">Description du projet</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        {{ $project->description ?? 'Aucune description fournie pour ce projet.' }}
                    </p>
                </div>

                <!-- Liste des Sous-Projets de l'ONG -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                            <i class="las la-stream text-gray-400 text-lg"></i> Structure des sous-projets associés
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($project->subProjects as $sub)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <div class="space-y-0.5">
                                    <h4 class="text-sm font-bold text-gray-800">{{ $sub->name }}</h4>
                                    <p class="text-xs text-gray-400">{{ $sub->activities_count }} activité(s)</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $sub->status === 'Terminé' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                                    {{ $sub->status }}
                                </span>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-gray-400 italic">Aucun sous-projet rattaché.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Colonne droite (1/3 de l'espace) : Gestion & Équipe -->
            <div class="space-y-6">
                <!-- Chef de projet -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Responsable de Projet</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-black text-sm flex items-center justify-center uppercase shadow-sm">
                            {{ Str::limit($project->manager->name ?? 'N', 2, '') }}
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">{{ $project->manager->name ?? 'Non assigné' }} 
                                {{ $project->manager->first_name ?? ' ' }}
                            </h4>
                            <p class="text-xs text-gray-400">{{ $project->manager->job_title ?? 'Pas de titre' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Membres de l'équipe assignés -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-900 text-sm">Équipe de déploiement ({{ $project->users_count }})</h3>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
                        @forelse($project->users as $user)
                            <a href="{{ route('users.show', ['userId' => $user->id]) }}" title="Voir le profil"
                                wire:navigate class="p-3 flex items-center justify-between hover:bg-gray-50/70 transition">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gray-200 text-gray-700 font-bold text-xs flex items-center justify-center uppercase">
                                        {{ Str::limit($user->name, 2, '') }}
                                    </div>
                                    <span class="text-xs font-semibold text-gray-800">{{ $user->name .' '. $user->first_name }}</span>
                                </div>
                                <span class="text-[10px] bg-gray-100 text-gray-500 font-medium px-2 py-0.5 rounded-full">
                                    {{ $user->activities->count() ?? 0 }} activité(s) : 
                                    <span class="font-bold text-gray-700">{{ floor($user->activities->sum('duration')) }}h : {{ round(($user->activities->sum('duration') - floor($user->activities->sum('duration'))) * 60) }}m</span>
                                </span>
                            </a>
                        @empty
                            <div class="p-4 text-center text-xs text-gray-400 italic">Aucun agent assigné à ce projet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Section des activités -->
    <div class="mt-4">
        <!-- En-tête -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="las la-tasks text-xl"></i>
                Activités du projet
                <span class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">
                    {{ $activityStats->total ?? 0 }}
                </span>
            </h2>
            <button wire:click="resetFilters" 
                    class="text-sm text-gray-500 hover:text-gray-700 transition-colors cursor-pointer">
                <i class="las la-undo"></i> Réinitialiser
            </button>
        </div>

        <!-- Filtres -->
        <div class="flex flex-wrap items-center gap-3 mb-4 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
            <!-- Filtre par statut -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Statut:</span>
                <select wire:model.live="activityFilter"  class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                    <option value="all">Tous</option>
                    <option value="brouillon">Brouillon</option>
                    <option value="soumis">Soumis</option>
                    <option value="approuvé">Approuvé</option>
                    <option value="rejeté">Rejeté</option>
                </select>
            </div>

            <!-- Barre de recherche -->
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <i class="las la-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                        wire:model.live.debounce="searchActivity" 
                        placeholder="Rechercher une activité..."
                        class="w-full pl-9 pr-3 py-1.5 text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <!-- Nombre par page -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Afficher:</span>
                <select wire:model.live="perPage" 
                        class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>

            <!-- Mini statistiques -->
            <div class="flex items-center gap-3 ml-auto text-sm">
                <span class="text-gray-500">
                    <i class="las la-pencil-alt"></i> {{ $activityStats->brouillon ?? 0 }}
                </span>
                <span class="text-yellow-600">
                    <i class="las la-paper-plane"></i> {{ $activityStats->soumis ?? 0 }}
                </span>
                <span class="text-green-600">
                    <i class="las la-check-circle"></i> {{ $activityStats->approuvé ?? 0 }}
                </span>
                <span class="text-red-600">
                    <i class="las la-times-circle"></i> {{ $activityStats->rejeté ?? 0 }}
                </span>
            </div>
        </div>

        <!-- Liste des activités -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @if($activities->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th wire:click="sortBy('titre')" 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700">
                                    <div class="flex items-center gap-1">
                                        Activité
                                        @if($sortBy === 'titre')
                                            <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('status')" 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700">
                                    <div class="flex items-center gap-1">
                                        Statut
                                        @if($sortBy === 'status')
                                            <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('user_id')" 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700">
                                    <div class="flex items-center gap-1">
                                        Assigné à
                                        @if($sortBy === 'user_id')
                                            <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('activity_date')" 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700">
                                    <div class="flex items-center gap-1">
                                        Date
                                        @if($sortBy === 'activity_date')
                                            <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('duration')" 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700">
                                    <div class="flex items-center gap-1">
                                        Durée
                                        @if($sortBy === 'duration')
                                            <i class="las la-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($activities as $activity)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-start gap-2">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $activity->titre ?? $activity->name ?? 'Sans titre' }}
                                                </div>
                                                @if($activity->description)
                                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                        {{ Str::limit($activity->description, 100) }}
                                                    </p>
                                                @endif
                                                <div class="flex items-center gap-2 mt-1">
                                                    @if($activity->subProject)
                                                        <span class="text-xs text-gray-400">
                                                            <i class="las la-folder"></i> {{ $activity->subProject->name }}
                                                        </span>
                                                    @endif
                                                    @if($activity->activityType)
                                                        <span class="text-xs text-gray-400">
                                                            <i class="las la-tag"></i> {{ $activity->activityType->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'brouillon' => 'bg-gray-100 text-gray-800',
                                                'soumis' => 'bg-yellow-100 text-yellow-800',
                                                'approuvé' => 'bg-green-100 text-green-800',
                                                'rejeté' => 'bg-red-100 text-red-800',
                                            ];
                                            $statusIcons = [
                                                'brouillon' => 'la-pencil-alt',
                                                'soumis' => 'la-paper-plane',
                                                'approuvé' => 'la-check-circle',
                                                'rejeté' => 'la-times-circle',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$activity->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            <i class="las {{ $statusIcons[$activity->status] ?? 'la-circle' }}"></i>
                                            {{ ucfirst($activity->status ?? 'Non défini') }}
                                        </span>
                                        @if($activity->status === 'rejeté' && $activity->rejection_reason)
                                            <div class="text-xs text-red-500 mt-1">
                                                <i class="las la-info-circle"></i> {{ Str::limit($activity->rejection_reason, 30) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            @if($activity->user)
                                                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-indigo-600">
                                                        {{ substr($activity->user->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <span class="text-sm text-gray-700">
                                                    {{ $activity->user->name }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">Non assigné</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-700">
                                            @if($activity->activity_date)
                                                <span class="text-xs">{{ $activity->activity_date->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-xs text-gray-400">Non définie</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-700">
                                            @if($activity->duration)
                                                <span class="text-xs font-medium">
                                                    {{ floor($activity->duration) }}h : {{ round(($activity->duration - floor($activity->duration)) * 60) }}m
                                                </span>
                                            @elseif($activity->start_time && $activity->end_time)
                                                @php
                                                    $start = \Carbon\Carbon::parse($activity->start_time);
                                                    $end = \Carbon\Carbon::parse($activity->end_time);
                                                    $diff = $start->diffInHours($end) . 'h' . $start->diffInMinutes($end) % 60 . 'min';
                                                @endphp
                                                <span class="text-xs">{{ $diff }}</span>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $activities->links() }}
                </div>
            @else
                <!-- Message vide -->
                <div class="text-center py-12">
                    <i class="las la-tasks text-5xl text-gray-300"></i>
                    <p class="mt-2 text-gray-500">Aucune activité trouvée pour ce projet.</p>
                    <p class="text-sm text-gray-400">
                        @if(!empty($searchActivity))
                            Essayez de modifier vos filtres de recherche.
                        @else
                            Créez votre première activité.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>