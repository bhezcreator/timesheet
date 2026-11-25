<div class="p-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Fiche du personnel
        </h2>
    </x-slot>

    {{-- Bouton Retour --}}
    <div class="flex items-center justify-between mb-3 p-0">
        <button type="button"
                onclick="history.back()"
                class="inline-flex items-center gap-2 text-sm cursor-pointer text-gray-500 font-bold hover:text-indigo-600 transition-colors">
            <i class="las la-arrow-left text-base"></i> Retour
        </button>

        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }} mr-1.5"></span>
            {{ $user->is_active ? 'Compte Actif' : 'Compte Inactif' }}
        </span>
    </div>

    {{-- En-tête de la Fiche Profil --}}
    <div class="bg-white rounded-2xl border border-gray-100 mb-4 p-6 shadow-xs flex flex-col md:flex-row items-center md:items-start gap-6">
        {{-- Gestion de la Photo / Icône --}}
        <div class="shrink-0">
            @if($user->photo && Storage::disk('public')->exists($user->photo))
                <img src="{{ Storage::url($user->photo) }}" alt="Photo de {{ $user->first_name }}" class="w-24 h-24 rounded-2xl object-cover border border-gray-100 shadow-inner">
            @else
                <div class="w-24 h-24 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center font-bold text-2xl tracking-wide uppercase shadow-2xs">
                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->name, 0, 1)) }}
                </div>
            @endif
        </div>

        {{-- Identité principale --}}
        <div class="flex-1 text-center md:text-left space-y-1.5">
            <div class="space-y-0.5">
                <span class="text-xs font-mono font-bold text-gray-400">N° ORDRE : {{ $user->num_order ?? '-' }}</span>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $user->first_name }} {{ $user->name }} {{ $user->last_name }}</h1>
                <p class="text-sm font-semibold text-indigo-600 flex items-center justify-center md:justify-start gap-1">
                    <i class="las la-briefcase text-base"></i> {{ $user->job_title ?? 'Poste non défini' }}
                </p> 
                <span class="flex items-center gap-1 text-xs text-gray-500">
                    <i class="las la-building text-base"></i>
                    {{ $user->office->name ?? 'Bureau non défini' }}
                </span>
            </div>

            <div class="text-xs text-gray-500 flex flex-wrap justify-center md:justify-start gap-x-4 gap-y-1 pt-1">
                <span class="flex items-center gap-1"><i class="las la-envelope text-sm text-gray-400"></i> {{ $user->email }}</span>
                @if($user->supervisor)
                    <span class="flex items-center gap-1">
                        <i class="las la-user-tie text-sm text-gray-400"></i>
                        Superviseur : <strong class="text-gray-700 font-semibold">{{ $user->supervisor->first_name }} {{ $user->supervisor->name }}</strong>
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Layout en Grille Asymétrique --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- COLONNE GAUCHE : Informations complémentaires & Rapports --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Signature numérique --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="las la-signature text-sm"></i> Signature électronique
                </h3>
                <div class="border border-dashed border-gray-200 rounded-xl p-4 bg-gray-50/50 flex items-center justify-center min-h-[100px]">
                    @if($user->signature)
                        <img src="{{ Storage::url($user->signature) }}" alt="Signature" class="max-h-16 object-contain">
                    @else
                        <span class="text-xs text-gray-400 italic">Aucune signature enregistrée</span>
                    @endif
                </div>
            </div>

        </div>

        {{-- COLONNE DROITE : Rapports Mensuels --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="las la-file-invoice text-sm"></i> Rapports mensuels ({{ $user->monthlyReports->count() }})
                </h3>
                @if($user->monthlyReports->isNotEmpty())
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        @foreach($user->monthlyReports as $report)
                            <div class="flex items-center justify-between p-2.5 bg-gray-50 border border-gray-100 rounded-xl">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-gray-800 block truncate">{{ $report->title ?? 'Rapport Mensuel' }}</span>
                                    <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($report->created_at)->format('d/m/Y') }}</span>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $report->status === 'validated' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $report->status }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic py-2">Aucun rapport soumis pour le moment.</p>
                @endif
            </div>
        </div>
        {{-- Fin Colonne Droite --}}
    </div> {{-- Fin Grille Principale --}}

    {{-- Subordonnés (Équipe gérée) --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 my-4 shadow-xs">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
            <i class="las la-users text-sm"></i> Équipe supervisée ({{ $user->subordinates->count() }})
        </h3>
        @if($user->subordinates->isNotEmpty())
            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                @foreach($user->subordinates as $subordinate)
                    <div class="flex items-center gap-2.5 p-2 bg-gray-50 border border-gray-100 rounded-xl">
                        <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center uppercase">
                            {{ substr($subordinate->first_name, 0, 1) }}{{ substr($subordinate->name, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-xs font-bold text-gray-800 truncate">{{ $subordinate->first_name }} {{ $subordinate->name }}</h4>
                            <p class="text-[10px] text-gray-500 truncate">{{ $subordinate->job_title }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-gray-400 italic py-2">Ne supervise aucun personnel actuellement.</p>
        @endif
    </div>

    {{-- Présentation des activités --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6 my-4 shadow-xs">

        {{-- En-tête --}}
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                <i class="las la-project-diagram text-lg text-indigo-500"></i>
                Projets & Sous-projets
            </h3>
            <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
                {{ $user->projects->count() + $orphanSubProjects->count() }}
            </span>
        </div>

        @if($user->projects->isNotEmpty() || $orphanSubProjects->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-separate border-spacing-0">

                    <thead>
                        <tr>
                            <th class="text-left py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                Projet / Activité
                            </th>
                            <th class="text-center py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-24">
                                Activités
                            </th>
                            <th class="text-right py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-24">
                                Heures
                            </th>
                        </tr>
                    </thead>

                    {{-- ==================== PROJETS ==================== --}}
                    @foreach($user->projects as $project)
                        @php
                            $stat        = $activityStats->get($project->id);
                            $pActivities = (int)   ($stat->activities_count ?? 0);
                            $pHours      = (float) ($stat->total_hours ?? 0);
                            $directActs  = $activitiesByProject->get($project->id, collect());
                        @endphp

                        <tbody x-data="{ open: false }" class="border-b border-gray-100">

                            {{-- Ligne PROJET --}}
                            <tr @click="open = !open" class="cursor-pointer hover:bg-gray-50/60 transition-colors">
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <i class="las la-angle-right text-gray-400 text-xs transition-transform" :class="open ? 'rotate-90' : ''"></i>
                                        <span class="font-mono bg-white border border-gray-200 px-2 py-0.5 rounded text-[10px] text-gray-500 font-semibold tracking-wider">
                                            {{ $project->code }}
                                        </span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $project->name }}</span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $project->pivot->role ?? 'Membre' }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-1 pl-5">
                                        Affecté le
                                        <strong>{{ $project->pivot->assigned_at ? \Carbon\Carbon::parse($project->pivot->assigned_at)->format('d/m/Y') : '—' }}</strong>
                                        @if($project->pivot->ended_at)
                                            · Fin <strong>{{ \Carbon\Carbon::parse($project->pivot->ended_at)->format('d/m/Y') }}</strong>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-gray-800">
                                        <i class="las la-tasks text-blue-500 text-sm"></i>
                                        {{ $pActivities }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="text-xs font-bold text-gray-800">
                                        {{ floor($pHours) }}h : {{ round(($pHours - floor($pHours)) * 60) }}m
                                    </span>
                                </td>
                            </tr>

                            {{-- Activités DIRECTES du projet --}}
                            @foreach($directActs as $activity)
                                <tr x-show="open" x-cloak class="bg-gray-50/20">
                                    <td class="py-1.5 px-3 pl-10">
                                        <div class="flex items-center gap-2 text-xs">
                                            <i class="las la-circle text-[6px] text-gray-400"></i>
                                            <span class="text-gray-700 truncate">{{ $activity->titre }}</span>
                                            @if($activity->activity_date)
                                                <span class="text-[10px] text-gray-400 shrink-0">
                                                    · {{ \Carbon\Carbon::parse($activity->activity_date)->format('d/m/Y') }}
                                                </span>
                                            @endif
                                            <span class="shrink-0 text-[9px] font-semibold px-1.5 py-0.5 rounded border
                                                @switch($activity->status)
                                                    @case('approuvé') bg-emerald-50 text-emerald-700 border-emerald-100 @break
                                                    @case('soumis')   bg-blue-50 text-blue-700 border-blue-100 @break
                                                    @case('rejeté')   bg-red-50 text-red-700 border-red-100 @break
                                                    @default          bg-gray-100 text-gray-600 border-gray-200
                                                @endswitch">
                                                {{ $activity->status }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-1.5 px-3 text-center text-[10px] text-gray-300">—</td>
                                    <td class="py-1.5 px-3 text-right text-[11px] text-gray-500">
                                        {{ floor($activity->duration) }}h : {{ round(($activity->duration - floor($activity->duration)) * 60) }}m
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Sous-projets du projet --}}
                            @foreach($subProjectsByProject->get($project->id, collect()) as $subProject)
                                @php
                                    $sStat       = $subActivityStats->get($subProject->id);
                                    $sActivities = (int)   ($sStat->activities_count ?? 0);
                                    $sHours      = (float) ($sStat->total_hours ?? 0);
                                    $subActs     = $activitiesBySubProject->get($subProject->id, collect());
                                @endphp

                                <tr x-show="open" x-cloak class="bg-gray-50/40 hover:bg-gray-50/70 transition-colors">
                                    <td class="py-2 px-3 pl-8">
                                        <div class="flex items-center gap-2">
                                            <i class="las la-angle-right text-gray-300 text-xs"></i>
                                            <span class="text-xs font-medium text-gray-700">{{ $subProject->name }}</span>
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded border
                                                {{ $subProject->status === 'actif'
                                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                                    : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                                {{ $subProject->status }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3 text-center text-xs text-gray-600">{{ $sActivities }}</td>
                                    <td class="py-2 px-3 text-right text-xs text-gray-600">
                                        {{ floor($sHours) }}h : {{ round(($sHours - floor($sHours)) * 60) }}m
                                    </td>
                                </tr>

                                {{-- Activités du sous-projet --}}
                                @foreach($subActs as $activity)
                                    <tr x-show="open" x-cloak class="bg-gray-50/60">
                                        <td class="py-1.5 px-3 pl-14">
                                            <div class="flex items-center gap-2 text-xs">
                                                <i class="las la-circle text-[6px] text-gray-300"></i>
                                                <span class="text-gray-700 truncate">{{ $activity->titre }}</span>
                                                @if($activity->activity_date)
                                                    <span class="text-[10px] text-gray-400 shrink-0">
                                                        · {{ \Carbon\Carbon::parse($activity->activity_date)->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                                <span class="shrink-0 text-[9px] font-semibold px-1.5 py-0.5 rounded border
                                                    @switch($activity->status)
                                                        @case('approuvé') bg-emerald-50 text-emerald-700 border-emerald-100 @break
                                                        @case('soumis')   bg-blue-50 text-blue-700 border-blue-100 @break
                                                        @case('rejeté')   bg-red-50 text-red-700 border-red-100 @break
                                                        @default          bg-gray-100 text-gray-600 border-gray-200
                                                    @endswitch">
                                                    {{ $activity->status }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-1.5 px-3 text-center text-[10px] text-gray-300">—</td>
                                        <td class="py-1.5 px-3 text-right text-[11px] text-gray-500">
                                             {{ floor($activity->duration) }}h : {{ round(($activity->duration - floor($activity->duration)) * 60) }}m
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    @endforeach

                    {{-- ==================== SOUS-PROJETS ORPHELINS ==================== --}}
                    @if($orphanSubProjects->isNotEmpty())
                        <tbody x-data="{ open: false }">
                            <tr @click="open = !open" class="cursor-pointer bg-amber-50/40 hover:bg-amber-50/70 border-b border-amber-100">
                                <td colspan="3" class="py-2 px-3 text-[10px] font-bold text-amber-800 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="las la-angle-right text-amber-500 text-xs transition-transform"
                                        :class="open ? 'rotate-90' : ''"></i>
                                        <i class="las la-exclamation-triangle text-amber-500"></i>
                                        Sous-projets hors projet ({{ $orphanSubProjects->count() }})
                                    </div>
                                </td>
                            </tr>

                            @foreach($orphanSubProjects as $subProject)
                                @php
                                    $sStat       = $subActivityStats->get($subProject->id);
                                    $sActivities = (int)   ($sStat->activities_count ?? 0);
                                    $sHours      = (float) ($sStat->total_hours ?? 0);
                                    $subActs     = $activitiesBySubProject->get($subProject->id, collect());
                                @endphp

                                <tr x-show="open" x-cloak class="bg-amber-50/20 hover:bg-amber-50/50">
                                    <td class="py-2 px-3 pl-8">
                                        <div class="flex items-center gap-2">
                                            <i class="las la-angle-right text-gray-300 text-xs"></i>
                                            <span class="text-xs font-medium text-gray-700">{{ $subProject->name }}</span>
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded border
                                                {{ $subProject->status === 'actif'
                                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                                    : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                                {{ $subProject->status }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3 text-center text-xs text-gray-600">{{ $sActivities }}</td>
                                    <td class="py-2 px-3 text-right text-xs text-gray-600">
                                        {{ floor($sHours) }}h : {{ round(($sHours - floor($sHours)) * 60) }}m
                                    </td>
                                </tr>

                                @foreach($subActs as $activity)
                                    <tr x-show="open" x-cloak class="bg-amber-50/40">
                                        <td class="py-1.5 px-3 pl-14">
                                            <div class="flex items-center gap-2 text-xs">
                                                <i class="las la-circle text-[6px] text-gray-300"></i>
                                                <span class="text-gray-700 truncate">{{ $activity->titre }}</span>
                                                @if($activity->activity_date)
                                                    <span class="text-[10px] text-gray-400 shrink-0">
                                                        · {{ \Carbon\Carbon::parse($activity->activity_date)->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                                <span class="shrink-0 text-[9px] font-semibold px-1.5 py-0.5 rounded border
                                                    @switch($activity->status)
                                                        @case('approuvé') bg-emerald-50 text-emerald-700 border-emerald-100 @break
                                                        @case('soumis')   bg-blue-50 text-blue-700 border-blue-100 @break
                                                        @case('rejeté')   bg-red-50 text-red-700 border-red-100 @break
                                                        @default          bg-gray-100 text-gray-600 border-gray-200
                                                    @endswitch">
                                                    {{ $activity->status }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-1.5 px-3 text-center text-[10px] text-gray-300">—</td>
                                        <td class="py-1.5 px-3 text-right text-[11px] text-gray-500">
                                            {{ floor($activity->duration) }}h : {{ round(($activity->duration - floor($activity->duration)) * 60) }}m
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    @endif

                    {{-- ==================== TOTAL ==================== --}}
                    <tfoot>
                        <tr class="bg-indigo-50">
                            <td class="py-3 px-3 rounded-bl-lg text-sm font-bold text-indigo-900">
                                <i class="las la-calculator text-indigo-600 mr-1"></i> Total général
                            </td>
                            <td class="py-3 px-3 text-center text-sm font-bold text-indigo-900">
                                {{ $grandActivities }}
                            </td>
                            <td class="py-3 px-3 rounded-br-lg text-right text-sm font-bold text-indigo-900">
                                 {{ floor($grandHours) }}h : {{ round(($grandHours - floor($grandHours)) * 60) }}m
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8 border border-dashed border-gray-200 rounded-xl bg-gray-50/50">
                <i class="las la-folder-open text-gray-300 text-3xl mb-1.5 block"></i>
                <p class="text-xs text-gray-400 font-medium">Aucun projet ou sous-projet assigné à ce compte.</p>
            </div>
        @endif

    </div>

    <!-- Liste des présences -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="las la-list text-indigo-500"></i>
                Détail des présences
                <span class="text-sm font-normal text-gray-400">
                    ({{ $attendances->total() }})
                </span>
            </h3>
            <button wire:click="exportCsv" 
                    class="px-4 py-2 text-sm bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-colors flex items-center gap-2">
                <i class="las la-file-csv"></i>
                Exporter CSV
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Heure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bureau</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motif</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localisation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $attendance->attendance_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $attendance->recorded_at->format('H:i:s') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $attendance->office?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    <i class="las {{ $this->getTypeIcon($attendance->type) }}"></i>
                                    {{ $this->getTypeLabel($attendance->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium 
                                    bg-{{ $this->getStatusColor($attendance->status) }}-50 
                                    text-{{ $this->getStatusColor($attendance->status) }}-700">
                                    <span class="w-1.5 h-1.5 rounded-full 
                                        bg-{{ $this->getStatusColor($attendance->status) }}-500"></span>
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-[150px] truncate">
                                {{ $attendance->reason ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($attendance->latitude && $attendance->longitude)
                                    <span class="flex items-center gap-1">
                                        <i class="las la-map-pin text-indigo-400"></i>
                                        {{ number_format($attendance->latitude, 4) }},
                                        {{ number_format($attendance->longitude, 4) }}
                                        @if($attendance->distance)
                                            <span class="text-xs text-gray-400">
                                                ({{ number_format($attendance->distance, 0) }}m)
                                            </span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="las la-calendar-plus text-4xl block mb-2 text-gray-300"></i>
                                <p class="text-sm">Aucune présence enregistrée</p>
                                <p class="text-xs text-gray-400 mt-1">Scannez un QR code pour commencer</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            <x-ui.pagination :paginator="$attendances" />
        </div>
    </div>
</div> {{-- Fin Conteneur Global --}}

