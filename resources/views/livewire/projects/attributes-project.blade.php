<div class="space-y-6 w-full p-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Attribution des projets
        </h2>
    </x-slot>

    <x-ui.breadcrumb :items="[
        [
            'label' => 'Tableau de bord',
            'url'   => route('dashboard')
        ],
        [
            'label' => 'Personnels',
            'url'   => route('users.index')
        ],
        [
            'label' => 'Attribution des projets'
        ]
    ]" />

    {{-- En-tête informatif sur l'utilisateur cible --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Affectation des Projets</h1>
                <p class="text-sm text-gray-500">
                    Configuration des accès pour le personnel : <span class="font-semibold text-gray-800">{{ $user->first_name }} {{ $user->name }}</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 w-full md:w-auto">
            <x-ui.forms.input wire:model.live.debounce.300ms="search" placeholder="Filtrer les projets..." class="w-full md:w-64" />
        </div>
    </div>

    {{-- Alertes Flash système --}}
    @if (session()->has('success'))
        <x-ui.alert type="success" class="mb-4 mt-8">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @error('permission')
        <x-ui.alert type="error" class="mb-4 mt-8">
            {{ $message }}
        </x-ui.alert>
    @enderror

    {{-- Grille principale moderne --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @foreach($projects as $project)
            <div class="flex flex-col gap-2 bg-white rounded-2xl border transition-all duration-200 shadow-sm {{ ($selectedProjects[$project->id] ?? false) ? 'border-blue-200 ring-2 ring-blue-50/50' : 'border-gray-100' }}" wire:key="project-card-{{ $project->id }}">

                {{-- Section Haute : Le Projet Parent --}}
                <div class="p-5 border-b border-gray-100">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            {{-- Checkbox d'activation du Projet --}}
                            <div class="relative flex items-center shrink-0 mt-0.5">
                                {{-- Case à cocher native masquée mais accessible --}}
                                <input
                                    type="checkbox"
                                    id="project-{{ $project->id }}"
                                    wire:model.lezy="selectedProjects.{{ $project->id }}"
                                    wire:change="toggleProject({{ $project->id }})"
                                    class="peer absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                >

                                {{-- Le fond du bouton Switch --}}
                                <div class="w-10 h-6 bg-gray-200 rounded-full transition-colors duration-200 ease-in-out
                                            peer-checked:bg-blue-600
                                            peer-hover:bg-gray-300 peer-checked:peer-hover:bg-blue-700
                                            peer-focus:ring-4 peer-focus:ring-blue-100 shadow-inner">
                                </div>

                                {{-- La pastille blanche mobile du Switch --}}
                                <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-200 ease-in-out
                                            peer-checked:translate-x-4">
                                </div>
                            </div>

                            <div>
                                <label for="project-{{ $project->id }}" class="font-bold text-gray-900 text-base block cursor-pointer select-none">
                                    {{ $project->name }}
                                </label>
                                <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-[11px] text-gray-500 tracking-wider inline-block mt-1">
                                    {{ $project->code }}
                                </span>
                            </div>
                        </div>

                        {{-- Sélecteur de rôle (S'affiche uniquement si le projet est coché) --}}
                        @if($selectedProjects[$project->id] ?? false)
                            <div class="shrink-0">
                                <select
                                    wire:model="projectRoles.{{ $project->id }}"
                                    class="text-xs font-semibold bg-blue-50/60 text-blue-800 border border-blue-100 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                >
                                    <option value="personnel">Personnel</option>
                                    <option value="superviseur">Superviseur</option>
                                    <option value="responsable">Responsable</option>
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Section Centrale : Liste des Sous-Projets du lot --}}
                <div class="flex-1 p-5 bg-gray-50/40 space-y-3">
                    <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="las la-cubes text-sm"></i> Sous-projets disponibles
                    </h3>

                    @if($project->subProjects->isNotEmpty())
                        <div class="grid grid-cols-1 gap-2.5 max-h-60 overflow-y-auto pr-1">
                            @foreach($project->subProjects as $subProject)
                                <div class="flex items-center justify-between p-3 bg-white border rounded-xl shadow-xs transition-colors {{ ($selectedSubProjects[$subProject->id] ?? false) ? 'border-emerald-200 bg-emerald-50/10' : 'border-gray-100' }}">
                                    <div class="min-w-0 flex-1 pr-3">
                                        <h4 class="text-sm font-semibold text-gray-800 truncate">{{ $subProject->name }}</h4>
                                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $subProject->description ?? 'Aucune description' }}</p>
                                    </div>

                                    {{-- Bouton Bascule d'attribution rapide du sous-projet --}}
                                    <button
                                        type="button"
                                        wire:click="$set('selectedSubProjects.{{ $subProject->id }}', {{ !($selectedSubProjects[$subProject->id] ?? false) ? 'true' : 'false' }}); toggleSubProject({{ $subProject->id }}, {{ $project->id }})"
                                        class="shrink-0 text-xs px-3 py-1.5 rounded-lg cursor-pointer border font-medium transition-all shadow-2xs {{ ($selectedSubProjects[$subProject->id] ?? false) ? 'bg-emerald-600 border-emerald-600 text-white hover:bg-emerald-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50' }}"
                                    >
                                        {{ ($selectedSubProjects[$subProject->id] ?? false) ? 'Attribué ✓' : 'Attribuer' }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 bg-white/40 border border-dashed border-gray-200 rounded-xl text-xs text-gray-400 italic">
                            Aucun sous-projet rattaché.
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Actions globales du bas --}}
    @if (!$hideBtn)
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100 bg-white p-4 rounded-xl shadow-sm">
            <x-ui.button wire:click="save" size="lg" variant="success">
                {{-- S'affiche uniquement si la méthode save n'est pas en train de s'exécuter --}}
                <span wire:loading.remove wire:target="save">
                    <i class="las la-check-circle text-lg"></i> Valider les attributions
                </span>

                {{-- S'affiche EXCLUSIVEMENT lorsque la méthode save est en cours d'exécution --}}
                <span wire:loading wire:target="save">
                    <i class="las la-spinner animate-spin mr-1"></i> Validation en cours...
                </span>
            </x-ui.button>
        </div>
    @endif

    <div class="mt-5">
        <x-ui.pagination :paginator="$projects" />
    </div>
</div>
