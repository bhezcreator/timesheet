<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestion des horaires de travail
        </h2>
    </x-slot>

    <div class="w-full mt-0">
        <!-- Alertes de session -->
        @error('permission')
            <x-ui.alert type="error" class="mb-4 mt-8">
                {{ $message }}
            </x-ui.alert>
        @enderror

        <!-- Liste des horaires -->
        @if(!$schedules->count() && empty($search) && empty($filterOffice) && empty($filterDay))
            <x-ui.empty-state title="Aucun horaire disponible" description="Créez votre premier horaire de travail pour vos bureaux." icon="las la-clock">
                <x-slot:action>
                    <x-ui.button wire:click="openModal">
                        <i class="las la-plus mr-1"></i> Créer un horaire
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <x-ui.table :columns="['N°', 'Nom', 'Bureau', 'Jour', 'Horaire', 'Période grâce', 'Statut', 'Actions']">
                <x-slot:header>
                    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-3">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                            Horaires de travail
                        </h1>
                        <x-ui.button wire:click="openModal" class="w-full sm:w-auto justify-center">
                            <i class="las la-plus mr-1.5 sm:mr-2"></i>
                            <span class="hidden xs:inline text-sm sm:text-base">Nouvel horaire</span>
                            <span class="inline xs:hidden text-sm">Nouveau</span>
                        </x-ui.button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                        <x-ui.forms.input 
                            wire:model.live="search" 
                            placeholder="Rechercher un horaire..." 
                            icon="las la-search"
                        />
                        
                        <x-ui.forms.select 
                            wire:model.live="filterOffice" 
                            placeholder="Filtrer par bureau"
                        >
                            <option value="">Tous les bureaux</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}">{{ $office->name }}</option>
                            @endforeach
                        </x-ui.forms.select>

                        <x-ui.forms.select 
                            wire:model.live="filterDay" 
                            placeholder="Filtrer par jour"
                        >
                            <option value="">Tous les jours</option>
                            @foreach($daysOfWeek as $key => $day)
                                <option value="{{ $key }}">{{ $day }}</option>
                            @endforeach
                        </x-ui.forms.select>

                        <div class="flex items-center gap-2">
                            <x-ui.button 
                                variant="outline" 
                                wire:click="resetFilters" 
                                class="w-full justify-center"
                                title="Réinitialiser les filtres"
                            >
                                <i class="las la-times mr-1"></i>
                                <span class="hidden sm:inline">Réinitialiser</span>
                            </x-ui.button>
                        </div>
                    </div>

                    @if(session('success'))
                        <br>
                        <x-ui.alert type="success" class="mb-4 mt-8">
                            {{ session('success') }}
                        </x-ui.alert>
                        <br>
                    @endif
                </x-slot:header>

                <tbody>
                    @foreach($schedules as $schedule)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="schedule-{{ $schedule->id }}">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-400 align-top">
                                {{ ($schedules->currentPage() - 1) * $schedules->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900 align-top">
                                <div class="flex items-center gap-2">
                                    <i class="las la-calendar-alt text-indigo-500 text-lg"></i>
                                    {{ $schedule->name }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-600 align-top">
                                <div class="flex items-center gap-2">
                                    <i class="las la-building text-gray-400"></i>
                                    {{ $schedule->office->name ?? 'N/A' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-600 align-top">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $daysOfWeek[$schedule->day_of_week] ?? 'N/A' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm align-top">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm font-medium text-gray-700">
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                                    </span>
                                    <i class="las la-arrow-right text-gray-400 text-sm"></i>
                                    <span class="font-mono text-sm font-medium text-gray-700">
                                        {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm align-top">
                                <span class="inline-flex items-center gap-1 text-gray-600">
                                    <i class="las la-hourglass-half text-amber-500"></i>
                                    {{ $schedule->grace_period }} min
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm align-top">
                                <button 
                                    wire:click="toggleActive({{ $schedule->id }})"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium transition-colors
                                    {{ $schedule->is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}"
                                    title="{{ $schedule->is_active ? 'Actif' : 'Inactif' }}"
                                >
                                    <span class="w-2 h-2 rounded-full {{ $schedule->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    {{ $schedule->is_active ? 'Actif' : 'Inactif' }}
                                </button>
                            </td>

                            <td class="px-6 py-4 text-sm space-x-1 whitespace-nowrap align-top text-right">
                                <x-ui.button variant="outline" wire:click="edit({{ $schedule->id }})" title="Modifier l'horaire">
                                    <i class="las la-edit"></i>
                                </x-ui.button>
                                <x-ui.button variant="danger" wire:click="confirmDelete({{ $schedule->id }})" title="Supprimer l'horaire">
                                    <i class="las la-trash"></i>
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <div class="mt-5">
                <x-ui.pagination :paginator="$schedules" />
            </div>
        @endif
    </div>

    <!-- MODALE : Création/Modification -->
    <x-ui.modal
        id="schedule-modal"
        :show="$showModal"
        title="{{ $scheduleId ? 'Modifier l\'horaire' : 'Ajouter un nouvel horaire' }}"
        size="xl"
        subtitle="{{ $scheduleId ? 'Modifiez les détails de l\'horaire de travail' : 'Configurez un nouvel horaire pour un bureau' }}"
    >
        <div class="space-y-6">
            <!-- Nom et Bureau -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-ui.forms.input
                        label="Nom de l'horaire"
                        name="name"
                        wire:model="name"
                        placeholder="Ex: Horaires bureau, Shift matin"
                    />
                    <x-ui.forms.error name="name" />
                </div>

                <div>
                    <x-ui.forms.searchable-select
                        wire:model="office_id"
                        name="office_id"
                        label="Bureau"
                        placeholder="-- Sélectionner un bureau --"
                        :selected="$office_id"
                        :live="true"
                        required
                        :options="$offices->map(fn($office) => [
                            'value' => $office->id,
                            'label' => $office->name,
                            'code'  => $office->code ?? null,
                            'address' => $office->address ?? null
                        ])->toArray()"
                    />
                    <x-ui.forms.error name="office_id" />
                </div>
            </div>

            <!-- Jour de la semaine -->
            <div>
                <x-ui.forms.searchable-select
                    wire:model="day_of_week"
                    name="day_of_week"
                    label="Jour de la semaine"
                    placeholder="-- Sélectionner un jour --"
                    :selected="$day_of_week"
                    :live="true"
                    required
                    :options="collect($daysOfWeek)->map(fn($day, $key) => [
                        'value' => $key,
                        'label' => $day,
                        'code' => $day
                    ])->values()->toArray()"
                />
                <x-ui.forms.error name="day_of_week" />
            </div>

            <!-- Horaires -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-ui.forms.input
                        label="Heure de début"
                        name="start_time"
                        type="time"
                        wire:model="start_time"
                        required
                    />
                    <x-ui.forms.error name="start_time" />
                </div>

                <div>
                    <x-ui.forms.input
                        label="Heure de fin"
                        name="end_time"
                        type="time"
                        wire:model="end_time"
                        required
                    />
                    <x-ui.forms.error name="end_time" />
                </div>
            </div>

            <!-- Période de grâce et Statut -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-ui.forms.input
                        label="Période de grâce (minutes)"
                        name="grace_period"
                        type="number"
                        wire:model="grace_period"
                        placeholder="15"
                        min="0"
                        max="60"
                    />
                    <x-ui.forms.error name="grace_period" />
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="las la-info-circle"></i>
                        Tolérance de retard en minutes (0-60)
                    </p>
                </div>

                <div>
                    <x-ui.forms.toggle
                        label="Actif"
                        name="is_active"
                        wire:model="is_active"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="las la-info-circle"></i>
                        Désactiver pour rendre l'horaire indisponible
                    </p>
                </div>
            </div>

            <!-- Aperçu -->
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-4 border border-indigo-100">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="las la-eye text-indigo-500"></i>
                    Aperçu de l'horaire
                </h4>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="las la-building text-gray-400"></i>
                        <span>{{ $offices->firstWhere('id', $office_id)?->name ?? 'Non défini' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="las la-calendar text-gray-400"></i>
                        <span>{{ $daysOfWeek[$day_of_week] ?? 'Non défini' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="las la-clock text-gray-400"></i>
                        <span>{{ $start_time }} - {{ $end_time }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="las la-hourglass-half text-gray-400"></i>
                        <span>{{ $grace_period }} min de grâce</span>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <x-ui.button
                    variant="outline"
                    wire:click="closeModal"
                    wire:loading.attr="disabled"
                    wire:target="closeModal"
                >
                    <span wire:loading.remove wire:target="closeModal">Annuler</span>
                    <span wire:loading wire:target="closeModal">
                        <i class="las la-spinner la-spin mr-1"></i>
                    </span>
                </x-ui.button>

                <x-ui.button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">
                        <i class="las la-save mr-1"></i> Sauvegarder
                    </span>
                    <span wire:loading wire:target="save">
                        <i class="las la-spinner la-spin mr-1"></i> Traitement...
                    </span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODALE : Confirmation de suppression -->
    <x-ui.modal
        :show="$showDeleteModal"
        id="delete-schedule-modal"
        title="Confirmation de suppression"
        size="sm"
    >
        <div class="text-center py-2">
            <i class="las la-exclamation-triangle text-red-500 text-5xl block mb-3 animate-pulse"></i>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Supprimer l'horaire ?</h3>
            <p class="text-sm text-gray-500 px-2">
                Êtes-vous sûr de vouloir supprimer définitivement l'horaire 
                <span class="font-bold text-gray-900">"{{ $deleteName }}"</span> ? 
                Cette action est irréversible.
            </p>
        </div>

        <x-slot:footer>
            <div class="flex justify-center w-full gap-3">
                <x-ui.button
                    variant="outline"
                    wire:click="closeDeleteModal"
                    wire:loading.attr="disabled"
                    wire:target="closeDeleteModal"
                >
                    Annuler
                </x-ui.button>

                <x-ui.button
                    variant="danger"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                >
                    <span wire:loading.remove wire:target="delete">
                        <i class="las la-trash mr-1"></i>
                        Confirmer la suppression
                    </span>
                    <span wire:loading wire:target="delete">
                        <i class="las la-spinner la-spin mr-1"></i>
                        Suppression...
                    </span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal>
</div>