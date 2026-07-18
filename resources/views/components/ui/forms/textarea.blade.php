@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'helper' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'rows' => 3,
    'maxlength' => null,
])

<div
    x-data="{
        value: '',
        init() {
            // CORRECTION 1 : Récupération immédiate et sécurisée de la valeur stockée dans Livewire
            this.value = $wire.get('{{ $name }}') ?? '';

            // Ajuste la hauteur dès l'affichage initial après injection de la valeur
            this.$nextTick(() => this.resize());

            // Espionne les mises à jour asynchrones de Livewire (ex: clic sur Modifier)
            this.$watch('$wire.{{ $name }}', (newValue) => {
                this.value = newValue ?? '';
                this.$nextTick(() => this.resize());
            });
        },
        resize() {
            this.$refs.textarea.style.height = 'auto';
            this.$refs.textarea.style.height = this.$refs.textarea.scrollHeight + 'px';
        }
    }"
    class="space-y-2 w-full"
>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-500 select-none">
            {{ $label }}
            @if($required)
                <span class="text-red-500 font-bold" title="Ce champ est obligatoire">*</span>
            @endif
        </label>
    @endif

    <!-- Zone de texte dynamique (CORRECTION 2 : Liaison Livewire gérée proprement par Alpine) -->
    <textarea
        {{ $attributes->merge([
            'class' => 'w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-700 shadow-sm transition-all duration-150 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-gray-50 disabled:cursor-not-allowed resize-none overflow-hidden focus:outline-none'
        ]) }}
        x-ref="textarea"
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        maxlength="{{ $maxlength }}"
        @disabled($disabled)
        @readonly($readonly)
        x-model="value"
        @input="resize(); $wire.set('{{ $name }}', value, false)" {{-- CORRECTION 3 : Met à jour Livewire en arrière-plan lors de la saisie sans appeler le serveur --}}
    ></textarea>

    <div class="flex justify-between items-start gap-4 min-h-[20px] px-1 select-none">
        <div class="flex-1">
            @if($helper)
                <p class="text-xs text-gray-500 italic">{{ $helper }}</p>
            @endif

            @error($name)
                <p class="text-xs font-medium text-red-600 mt-0.5">{{ $message }}</p>
            @enderror
        </div>

        @if($maxlength)
            <span
                class="text-xs font-mono font-medium transition-colors"
                :class="value.length >= {{ $maxlength }} * 0.9 ? 'text-red-500 font-bold' : 'text-gray-400'"
                x-text="value.length + ' / {{ $maxlength }}'"
            ></span>
        @endif
    </div>
</div>
