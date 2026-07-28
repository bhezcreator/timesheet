<div class="p-0 space-y-6">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rapports à validés
        </h2>
    </x-slot>
    <!-- Grille des Filtres -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-white p-4 border-t border-t-blue-700 rounded-2xl shadow-xs">
        <!-- Filtre Collaborateur -->
        <div class="relative w-full group" x-data="{ val: @entangle('selected_user_id').live }">
            <select x-model="val"
                    class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition">
                <option value="">Tous les collaborateurs</option>
                @foreach($subordinates as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->first_name }} {{ $sub->last_name }}</option>
                @endforeach
            </select>
            <div x-show="val" x-cloak class="absolute inset-y-0 right-3 flex items-center z-10">
                <button type="button" @click="val = ''" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                    <i class="las la-times-circle text-base"></i>
                </button>
            </div>
            <div x-show="!val" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                <i class="las la-user-friends text-base"></i>
            </div>
        </div>

        <!-- Filtre Mois -->
        <div class="relative w-full group" x-data="{ val: @entangle('month').live }">
            <select x-model="val"
                    class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition">
                <option value="">Tous les mois</option>
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}">{{ now()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <div x-show="val" x-cloak class="absolute inset-y-0 right-3 flex items-center z-10">
                <button type="button" @click="val = ''" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                    <i class="las la-times-circle text-base"></i>
                </button>
            </div>
            <div x-show="!val" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                <i class="las la-angle-down text-xs"></i>
            </div>
        </div>

        <!-- Filtre Année -->
        <div class="relative w-full group" x-data="{ val: @entangle('year').live }">
            <select x-model="val"
                    class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition">
                <option value="">Toutes les années</option>
                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
            <div x-show="val" x-cloak class="absolute inset-y-0 right-3 flex items-center z-10">
                <button type="button" @click="val = ''" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                    <i class="las la-times-circle text-base"></i>
                </button>
            </div>
            <div x-show="!val" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                <i class="las la-angle-down text-xs"></i>
            </div>
        </div>

        <!-- Filtre Projet -->
        <div class="relative w-full group" x-data="{ val: @entangle('selected_project_id').live }">
            <select x-model="val"
                    class="w-full appearance-none bg-white rounded-xl border border-gray-200 px-4 py-4 pr-10 text-xs font-medium text-gray-700 shadow-xs cursor-pointer hover:border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 focus:outline-none transition">
                <option value="all">Tous les projets</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
            <div x-show="val && val !== 'all'" x-cloak class="absolute inset-y-0 right-3 flex items-center z-10">
                <button type="button" @click="val = 'all'" class="text-gray-400 hover:text-red-500 transition cursor-pointer">
                    <i class="las la-times-circle text-base"></i>
                </button>
            </div>
            <div x-show="!val || val === 'all'" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 group-hover:text-gray-600 transition">
                <i class="las la-folder-open text-base"></i>
            </div>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden p-4 border-t border-t-blue-700 rounded-2xl">
        @if(!$reports->count())
            <x-ui.empty-state
                title="Aucun rapport trouvé"
                description="Aucun rapport n'a été soumis par les collaborateurs supervisés pour les critères sélectionnés."
                icon="las la-file-alt"
            />
        @else
            <x-ui.table :columns="['N°', 'Collaborateur', 'Période', 'Statut', 'Soumis le', 'Heures', 'Pièces jointes', 'Actions']">
                <tbody>
                    @foreach($reports as $report)
                        <tr wire:key="report-{{ $report->id }}" class="hover:bg-gray-50 transition-colors">
                            {{-- N° --}}
                            <td class="px-4 py-4 text-sm text-gray-400 font-semibold align-top">
                                {{ ($reports->currentPage() - 1) * $reports->perPage() + $loop->iteration }}
                            </td>

                            {{-- Collaborateur --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-700 overflow-hidden">
                                        @if($report->user?->photo)
                                            <img src="{{ Storage::url($report->user->photo) }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($report->user?->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($report->user?->last_name ?? 'N', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">
                                            {{ $report->user?->first_name }} {{ $report->user?->last_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $report->user?->job_title }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Période --}}
                            <td class="px-6 py-4 align-top">
                                <span class="font-medium text-gray-700">
                                    {{ now()->month($report->month)->translatedFormat('F') }} {{ $report->year }}
                                </span>
                            </td>

                            {{-- Statut --}}
                            <td class="px-6 py-4 align-top">
                                @switch($report->status)
                                    @case('brouillon')
                                        <x-ui.badge variant="secondary">Brouillon</x-ui.badge>
                                        @break
                                    @case('soumis')
                                        <x-ui.badge variant="warning">Soumis</x-ui.badge>
                                        @break
                                    @case('approuvé')
                                        <x-ui.badge variant="success">Approuvé</x-ui.badge>
                                        @break
                                    @case('rejeté')
                                        <x-ui.badge variant="danger">Rejeté</x-ui.badge>
                                        @break
                                    @default
                                        <x-ui.badge>{{ ucfirst($report->status) }}</x-ui.badge>
                                @endswitch
                            </td>

                            {{-- Date --}}
                            <td class="px-6 py-4 text-sm text-gray-600 align-top">
                                {{ $report->submitted_at?->format('d/m/Y H:i') }}
                            </td>

                            {{-- Heures --}}
                            <td class="px-6 py-4 align-top">
                                <x-ui.badge variant="info">
                                    {{ number_format($report->activities->sum('duration'), 2) }} h
                                </x-ui.badge>
                            </td>

                            {{-- Pièces jointes --}}
                            <td class="px-6 py-4 align-top">
                                @if($report->media->count())
                                    <x-ui.badge variant="primary">
                                        <i class="las la-paperclip mr-1"></i>
                                        {{ $report->media->count() }}
                                    </x-ui.badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right whitespace-nowrap align-top">
                                <div class="flex justify-end gap-2">
                                    @if ($report->status === "approuvé")
                                        <a href="{{ route('rapports.print', ['reportId' => $report->id]) }}"
                                            class="inline-flex items-center justify-center p-2 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-all"
                                            title="Imprimer le rapport">
                                            <i class="las la-print text-xl mx-1"></i> Imprimer
                                        </a>
                                    @endif

                                    @can('validations.effectuer')
                                        @if ($report->status === "soumis")
                                            <a href="{{ route('validations.show', $report->id) }}"
                                                class="inline-flex items-center justify-center p-2 rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 shadow-sm transition-all"
                                                title="Valider le rapport">
                                                <i class="las la-check-circle text-xl mx-1"></i> Validé rapport
                                            </a>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <div class="p-2">
                <x-ui.pagination :paginator="$reports" />
            </div>
        @endif
    </div>

</div>
