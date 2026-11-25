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

                {{-- Bureau --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Bureau <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="officeId"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                        <option value="">Choisir le bureau...</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}">
                                {{ $office->name }} ({{ $office->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('officeId') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Type <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="type"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                        <option value="arrivée">Arrivée</option>
                        <option value="départ">Départ</option>
                        <option value="pause">Pause</option>
                        <option value="reprise">Reprise</option>
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
                        <option value="présent">Présent</option>
                        <option value="retard">Retard</option>
                        <option value="absent">Absent</option>
                        <option value="congé">Congé</option>
                        <option value="mission">Mission</option>
                    </select>
                    @error('status') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- Date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" wire:model="attendanceDate"
                           max="{{ now()->format('Y-m-d') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                    @error('attendanceDate') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- Heure --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Heure <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" wire:model="recordedAt"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                    @error('recordedAt') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

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
                        <i class="las la-save mr-1"></i> Enregistrer la présence
                    </span>
                    <span wire:loading wire:target="save">
                        <i class="las la-spinner la-spin mr-1"></i> Enregistrement...
                    </span>
                </x-ui.button>
            </div>
        </div>
    </div>
</div>