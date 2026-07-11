@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Sélectionner...',
    'searchable' => true,
])

<div
    x-data="{
        open: false,
        search: '',
        selected: @js($selected),
        selectedLabel: '',
        selectedIcon: '',
        options: @js($options),
        init() {
            let option = this.options.find(item => item.value == this.selected);
            if (option) {
                this.selectedLabel = option.label;
                this.selectedIcon = option.icon ?? '';
            }
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.search = '';
                this.$nextTick(() => {
                    if (this.$refs.search) this.$refs.search.focus();
                });
            }
        },
        selectOption(option) {
            this.selected = option.value;
            this.selectedLabel = option.label;
            this.selectedIcon = option.icon ?? '';
            this.open = false;
        },
        get filteredOptions() {
            if (!this.search) return this.options;
            return this.options.filter(option => {
                return option.label.toLowerCase().includes(this.search.toLowerCase());
            });
        }
    }"
    @click.outside="open = false"
    class="relative w-full"
>
    <!-- Label -->
    @if($label)
        <label class="block mb-2 text-sm font-semibold text-gray-700">
            {{ $label }}
        </label>
    @endif

    <!-- Déclencheur (Trigger Button) -->
    <button
        type="button"
        @click="toggle()"
        class="w-full flex items-center justify-between rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm hover:border-blue-400 focus:ring-4 focus:ring-blue-100 transition focus:outline-none"
    >
        <div class="flex items-center gap-2">
            <template x-if="selectedIcon">
                <i :class="selectedIcon" class="text-gray-400"></i>
            </template>

            <span x-show="selectedLabel" x-text="selectedLabel" class="text-gray-700"></span>
            <span x-show="!selectedLabel" class="text-gray-400">{{ $placeholder }}</span>
        </div>

        <i class="las la-angle-down text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
    </button>

    <!-- Menu Déroulant (Dropdown) -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute {{ $label ? 'top-[calc(100%-8px)]' : 'top-full' }} z-50 mt-2 w-full rounded-xl border border-gray-200 bg-white shadow-xl overflow-hidden"
        style="display: none;"
    >
        <!-- Zone de Recherche -->
        @if($searchable)
            <div class="p-3 border-b border-gray-100">
                <div class="relative">
                    <i class="las la-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        x-ref="search"
                        x-model="search"
                        type="text"
                        placeholder="Rechercher..."
                        class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:outline-none"
                    >
                </div>
            </div>
        @endif

        <!-- Options List -->
        <div class="max-h-72 overflow-y-auto">
            <template x-for="option in filteredOptions" :key="option.value">
                <button
                    type="button"
                    @click="selectOption(option)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-blue-50 transition cursor-pointer"
                    :class="{ 'bg-blue-50/50': selected == option.value }"
                >
                    <template x-if="option.icon">
                        <i :class="option.icon" class="text-gray-400"></i>
                    </template>

                    <div class="flex-1">
                        <div x-text="option.label" class="font-medium text-gray-700"></div>
                        <template x-if="option.description">
                            <p x-text="option.description" class="text-xs text-gray-500 mt-0.5"></p>
                        </template>
                    </div>

                    <template x-if="selected == option.value">
                        <i class="las la-check text-blue-600"></i>
                    </template>
                </button>
            </template>

            <!-- Aucun résultat -->
            <div x-show="filteredOptions.length === 0" class="py-8 text-center text-sm text-gray-500">
                <i class="las la-search text-3xl block mb-2 text-gray-300"></i>
                Aucun résultat
            </div>
        </div>
    </div>

    <!-- Champ caché pour la soumission classique de formulaire -->
    <input type="hidden" name="{{ $name }}" :value="selected">
</div>
