<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Enregistrer une présence
        </h2>
    </x-slot>

    {{-- Retour --}}
    <div class="flex items-center justify-between mb-1 p-0">
        <a href="{{ route('activities.supervise', ['userId' => $user->id]) }}"
           wire:navigate
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors font-medium">
            <i class="las la-arrow-left text-base"></i> <strong>Retour</strong>
        </a>
    </div>

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-1 border-b border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                <i class="las la-user-check text-indigo-500 text-3xl align-middle mr-2"></i>
                Enregistrer une présence
            </h2>
            <p class="text-sm text-gray-400 mt-1 flex items-center gap-2">
                <i class="las la-user text-gray-300"></i>
                Personnel :
                <strong class="text-gray-700">
                    {{ $user->first_name ?? '' }} {{ $user->name }}
                </strong>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full border border-indigo-100">
                <i class="las la-calendar-check text-indigo-500"></i>
                {{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>

    <div class="w-full mt-0">

        {{-- Messages d'erreur globaux --}}
        @error('permission')
            <x-ui.alert type="error" class="mb-4">{{ $message }}</x-ui.alert>
            <br />
        @enderror

        @error('duplicate')
            <x-ui.alert type="error" class="mb-4">{{ $message }}</x-ui.alert>
            <br />
        @enderror

        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Toggle mode plage --}}
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between p-4 rounded-xl border-2 transition-all
                                {{ $isRangeMode ? 'border-indigo-300 bg-gradient-to-r from-indigo-50 to-white' : 'border-gray-200 bg-gray-50/60' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                        {{ $isRangeMode ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500' }}">
                                <i class="las la-calendar-alt text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Enregistrer sur une plage de dates</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Exclut automatiquement les <strong>dimanches</strong> et les <strong>jours non ouvrables</strong>
                                </p>
                            </div>
                        </div>
                        <button type="button"
                                wire:click="$toggle('isRangeMode')"
                                role="switch"
                                aria-checked="{{ $isRangeMode ? 'true' : 'false' }}"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                       {{ $isRangeMode ? 'bg-indigo-600' : 'bg-gray-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                         {{ $isRangeMode ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Type <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model.live="type"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                        <option value="arrivée">Arrivée</option>
                        <option value="départ">Départ</option>
                    </select>
                    @error('type') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- Statut --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Statut <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="status"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                        <option value="present">Présent</option>
                        <option value="retard">Retard</option>
                        <option value="absent">Absent</option>
                        <option value="congé">Congé</option>
                        <option value="mission">Mission</option>
                    </select>
                    @error('status') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- ========== MODE SIMPLE ========== --}}
                @if(! $isRangeMode)

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" wire:model.live="attendanceDate"
                               max="{{ now()->format('Y-m-d') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                        @error('attendanceDate') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Heure <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" wire:model="recordedAt"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                        @error('recordedAt') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    @if($singleDateWarning)
                        <div class="md:col-span-2 flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                            <i class="las la-exclamation-triangle text-base mt-0.5"></i>
                            <span>{{ $singleDateWarning }}</span>
                        </div>
                    @endif

                {{-- ========== MODE PLAGE ========== --}}
                @else

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Date de début <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" wire:model.live="attendanceDate"
                               max="{{ now()->format('Y-m-d') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                        @error('attendanceDate') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Date de fin <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" wire:model.live="endDate"
                               min="{{ $attendanceDate }}"
                               max="{{ now()->format('Y-m-d') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                        @error('endDate') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Heure <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" wire:model="recordedAt"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                        @error('recordedAt') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-end">
                        <div class="flex items-start gap-2 p-2.5 bg-indigo-50/60 border border-indigo-100 rounded-lg text-xs text-indigo-700 w-full">
                            <i class="las la-info-circle text-base mt-0.5"></i>
                            <span>L'heure sera appliquée à toutes les dates de la plage.</span>
                        </div>
                    </div>

                    {{-- Aperçu des jours à créer --}}
                    @if(! empty($previewDates))
                        <div class="md:col-span-2">
                            <div class="rounded-xl border border-gray-200 overflow-hidden">
                                <div class="bg-gradient-to-r from-gray-50 to-white px-4 py-2.5 border-b border-gray-200 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">
                                        <i class="las la-list-check text-indigo-500 mr-1"></i>
                                        {{ count($previewDates) }} jour(s) ouvrable(s) sélectionné(s)
                                    </span>
                                    <span class="text-[11px] text-gray-500">
                                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span> à créer
                                        <span class="inline-block w-2 h-2 rounded-full bg-amber-500 ml-3 mr-1"></span> doublon
                                    </span>
                                </div>
                                <div class="p-3 flex flex-wrap gap-1.5 max-h-48 overflow-y-auto">
                                    @foreach($previewDates as $p)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium border
                                                     {{ $p['exists']
                                                            ? 'bg-amber-50 text-amber-700 border-amber-200'
                                                            : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                            <i class="las {{ $p['exists'] ? 'la-exclamation-triangle' : 'la-check-circle' }} text-sm"></i>
                                            {{ $p['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @elseif($attendanceDate && $endDate)
                        <div class="md:col-span-2 flex items-start gap-2 p-3 bg-rose-50 border border-rose-200 rounded-lg text-sm text-rose-700">
                            <i class="las la-exclamation-circle text-base mt-0.5"></i>
                            <span>Aucun jour ouvrable dans cette plage (dimanches et jours non ouvrables exclus).</span>
                        </div>
                    @endif

                @endif

                {{-- Motif --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Motif <span class="text-gray-400 font-normal">(facultatif)</span>
                    </label>
                    <textarea wire:model="reason" rows="2"
                              placeholder="Ex : Mission client, retard justifié..."
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2"></textarea>
                    @error('reason') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- Info localisation --}}
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 p-3 bg-indigo-50/50 rounded-lg border border-indigo-100">
                        <i class="las la-info-circle text-indigo-500"></i>
                        <p class="text-xs text-gray-600">
                            Enregistrement manuel par superviseur — la vérification GPS n'est pas requise.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <x-ui.button variant="outline" wire:click="resetForm">
                    <i class="las la-undo mr-1"></i> Réinitialiser
                </x-ui.button>
                <x-ui.button wire:click="save" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">
                        <i class="las la-save mr-1"></i>
                        @if($isRangeMode) Enregistrer les présences @else Enregistrer la présence @endif
                    </span>
                    <span wire:loading wire:target="save">
                        <i class="las la-spinner la-spin mr-1"></i> Enregistrement...
                    </span>
                </x-ui.button>
            </div>
        </div>
    </div>
</div>