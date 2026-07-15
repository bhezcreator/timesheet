@props([
    'name',                             // Nom de la propriété Livewire (ex: 'project_id')
    'label' => null,                    // Texte d'en-tête (ex: 'Projet parent')
    'placeholder' => 'Sélectionner...',  // Texte par défaut si vide
    'options' => [],                    // Tableau d'options au format standardisé
    'selected' => null,                 // Valeur actuellement sélectionnée
    'required' => false,                // Astérisque de validation visuelle
    'live' => false,                    // Déclenche instantanément le serveur au clic si vrai
    'icon' => 'las la-project-diagram'  // Icône principale du bouton déclencheur
])

@php
    // Transformation des options pour une lecture fluide et sécurisée par Alpine.js
    $formattedOptions = collect($options)->map(fn($opt) => [
        'value'       => (string)$opt['value'],
        'label'       => str_replace("'", "\'", $opt['label']),
        'code'        => isset($opt['code']) ? str_replace("'", "\'", $opt['code']) : null,
        'description' => isset($opt['description']) ? str_replace("'", "\'", $opt['description']) : null,
    ])->toArray();

    // Recherche de l'option actuellement sélectionnée pour l'état d'affichage initial
    $initialOption = collect($formattedOptions)->firstWhere('value', (string)$selected);
    $initialName = $initialOption ? $initialOption['label'] : '';
    $initialCode = $initialOption ? $initialOption['code'] : '';
@endphp

<div class="relative w-full"
     x-data="{
        open: false,
        search: '',
        selected: @js((string)$selected),
        selectedName: @js($initialName),
        selectedCode: @js($initialCode),
        options: @js($formattedOptions),

        init() {
            // Écouteur pour mettre à jour l'UI si Livewire change la variable en arrière-plan
            this.$watch('$wire.' + @js($name), (value) => {
                this.selected = value ? String(value) : '';
                let option = this.options.find(item => item.value === this.selected);
                if (option) {
                    this.selectedName = option.label;
                    this.selectedCode = option.code ?? '';
                } else {
                    this.selectedName = '';
                    this.selectedCode = '';
                }
            });
        },

        selectOption(option) {
            this.selected = option.value;
            this.selectedName = option.label;
            this.selectedCode = option.code ?? '';
            this.open = false;
            this.search = '';

            // Envoi de la donnée à Livewire avec prise en compte dynamique du modificateur .live
            this.$wire.set(@js($name), this.selected, @js($live));
        },

        resetSelection() {
            this.selected = '';
            this.selectedName = '';
            this.selectedCode = '';
            this.open = false;
            this.search = '';
            this.$wire.set(@js($name), null, @js($live));
        }
    }"
    @click.outside="open = false">

    <!-- Label Moderne Épuré -->
    @if($label)
        <label class="block mb-2 text-sm font-semibold text-gray-500">
            {{ $label }}
            @if($required)
                <span class="text-red-500 font-bold" title="Ce champ est obligatoire">*</span>
            @endif
        </label>
    @endif

    <!-- Déclencheur (Trigger Button) Style Premium -->
    <div @click="open = !open"
        :class="open ? 'border-blue-500 ring-4 ring-blue-50 bg-white' : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-xs'"
        class="w-full flex items-center justify-between rounded-xl border px-4 py-3.5 shadow-xs cursor-pointer transition-all duration-200 select-none text-sm">

        <div class="flex items-center gap-2.5 truncate">
            <i class="{{ $icon }} text-gray-400 text-base" :class="selected ? 'text-blue-500' : ''"></i>

            <template x-if="selectedCode">
                <span class="text-[10px] font-mono font-bold uppercase bg-blue-50 Baba-700 px-1.5 py-0.5 rounded border border-blue-100/70 tracking-tight" x-text="selectedCode"></span>
            </template>

            <span class="truncate font-medium"
                :class="selected ? 'text-gray-900' : 'text-gray-400'"
                x-text="selectedName || @js($placeholder)">
            </span>
        </div>

        <i class="las la-unfold text-gray-400 text-sm transition-transform duration-200" :class="open ? 'rotate-180 text-blue-500' : ''"></i>
    </div>

    <!-- Menu Déroulant (Dropdown) Style Volant Premium -->
    <div x-show="open"
        x-transition:enter="transition ease-out duration-120"
        x-transition:enter-start="opacity-0 translate-y-1 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-85"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-98"
        class="absolute {{ $label ? 'top-[calc(100%-4px)]' : 'top-full' }} z-50 mt-2 w-full rounded-2xl bg-white p-2 shadow-xl border border-gray-100 space-y-2"
        style="display: none;">

        <!-- Option pour vider la sélection (Affichée uniquement si une option est sélectionnée) -->
        <div x-show="selected" class="p-1 bg-gray-50 border-b border-gray-100 rounded-t-xl">
            <button
                type="button"
                @click="resetSelection()"
                class="w-full text-left px-3 py-2 text-xs text-gray-500 hover:text-red-600 font-semibold transition cursor-pointer flex items-center gap-1.5">
                <i class="las la-times-circle text-sm"></i> Réinitialiser la sélection
            </button>
        </div>

        <!-- Input de Filtre Rapide Intégré -->
        <div class="relative flex items-center bg-gray-50/80 border border-gray-100 rounded-xl px-3 py-2 focus-within:border-blue-400 focus-within:bg-white focus-within:ring-4 focus-within:ring-blue-50 transition-all duration-150">
            <i class="las la-search text-gray-400 mr-2 text-sm"></i>
            <input type="text" x-ref="search" x-model="search" x-init="$watch('open', value => value && $nextTick(() => $refs.search.focus()))" placeholder="Rechercher dans les options..."
                class="w-full p-0 bg-transparent border-0 focus:ring-0 text-xs text-gray-900 placeholder-gray-400 focus:outline-none">

            <button type="button" x-show="search" @click="search = ''" class="text-gray-400 hover:text-gray-600">
                <i class="las la-times-circle text-xs"></i>
            </button>
        </div>

        <!-- Liste Défilante des Options -->
        <div class="max-h-56 overflow-y-auto space-y-0.5 pr-1 custom-scrollbar">
            <template x-for="option in options" :key="option.value">
                <div x-show="option.label.toLowerCase().includes(search.toLowerCase()) || (option.code && option.code.toLowerCase().includes(search.toLowerCase()))"
                    @click="selectOption(option)"
                    :class="selected === option.value ? 'bg-blue-50/80 text-blue-700 font-bold border-blue-100' : 'text-gray-700 hover:bg-gray-50 border-transparent hover:text-gray-900'"
                    class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-xs cursor-pointer border transition-all duration-150 group">

                    <div class="flex items-center gap-2.5 truncate">
                        <template x-if="option.code">
                            <span :class="selected === option.value ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-500 group-hover:bg-blue-50 group-hover:text-blue-700'"
                                class="font-mono font-semibold uppercase px-1.5 py-0.5 rounded text-[10px] tracking-tight transition-colors"
                                x-text="option.code">
                            </span>
                        </template>
                        <span class="truncate font-medium" x-text="option.label"></span>
                    </div>

                    <template x-if="selected === option.value">
                        <i class="las la-check text-blue-600 text-sm"></i>
                    </template>
                </div>
            </template>

            <!-- État Vide Raffiné si aucune correspondance -->
            <div x-show="search && !options.some(opt => opt.label.toLowerCase().includes(search.toLowerCase()) || (opt.code && opt.code.toLowerCase().includes(search.toLowerCase())))"
                class="flex flex-col items-center justify-center text-center text-gray-400 py-6 space-y-1">
                <i class="las la-search-minus text-2xl text-gray-300"></i>
                <span class="text-xs font-medium text-gray-500">Aucun résultat ne correspond.</span>
            </div>
        </div>
    </div>

    <!-- Champ caché pour formulaire -->
    <input type="hidden" name="{{ $name }}" :value="selected">
</div>
