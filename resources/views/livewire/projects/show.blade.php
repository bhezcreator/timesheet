<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tableau de bord
        </h2>
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
            'label' => 'Tableau de bord'
        ]
    ]" />

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mt-1 mb-1">
                {{ $project->name }}
            </h2>
            <span class="text-xs font-mono uppercase bg-blue-50 text-blue-700 px-2 py-0.5 rounded border border-blue-100">{{ $project->code }}</span>
        </div>
        <div>
            <x-ui.badge variant="{{ $project->status === 'Actif' ? 'success' : ($project->status === 'En pause' ? 'warning' : 'danger') }}">
                {{ \Illuminate\Support\Str::ucfirst($project->status) }}
            </x-ui.badge>
        </div>
    </div>

    <!-- Écran global du Dashboard -->
    <div class="w-full space-y-6 mt-8 pb-12">

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
                    <span class="text-3xl font-black text-gray-900">{{ $sub_projects_completed }}<span class="text-lg text-gray-400 font-normal"> / {{ $project->sub_projects_count }}</span></span>
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
                    <span class="text-3xl font-black text-gray-900">{{ $activities_completed }}<span class="text-lg text-gray-400 font-normal"> / {{ $project->activities_count }}</span></span>
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
                                    <p class="text-xs text-gray-400">{{ $sub->activities_count }} activité(s) contenue(s)</p>
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
                            <h4 class="text-sm font-bold text-gray-900">{{ $project->manager->name ?? 'Non assigné' }}</h4>
                            <p class="text-xs text-gray-400">Project Manager Senior</p>
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
                            <div class="p-3 flex items-center justify-between hover:bg-gray-50/70 transition">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gray-200 text-gray-700 font-bold text-xs flex items-center justify-center uppercase">
                                        {{ Str::limit($user->name, 2, '') }}
                                    </div>
                                    <span class="text-xs font-semibold text-gray-800">{{ $user->name }}</span>
                                </div>
                                <span class="text-[10px] bg-gray-100 text-gray-500 font-medium px-2 py-0.5 rounded-full">
                                    {{ $user->pivot->role ?? 'Collaborateur' }}
                                </span>
                            </div>
                        @empty
                            <div class="p-4 text-center text-xs text-gray-400 italic">Aucun agent assigné à ce projet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
