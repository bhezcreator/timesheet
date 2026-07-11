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
            // Capte la valeur initiale (Blade / Livewire) au chargement
            this.value = this.$refs.textarea.value;

            // Ajuste la hauteur dès l'affichage initial
            this.$nextTick(() => this.resize());

            // Espionne les mises à jour asynchrones de Livewire (ex: clic sur Modifier)
            this.$watch('$wire.{{ $name }}', (newValue) => {
                this.value = newValue ?? '';
                this.$nextTick(() => this.resize());
            });
        },
        resize() {
            // Réinitialise la hauteur pour recalculer le scrollHeight exact
            this.$refs.textarea.style.height = 'auto';
            // Applique la nouvelle hauteur basée sur le contenu réel
            this.$refs.textarea.style.height = this.$refs.textarea.scrollHeight + 'px';
        }
    }"
    class="space-y-2 w-full"
>
    <!-- Label avec astérisque de sécurité -->
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 select-none">
            {{ $label }}
            @if($required)
                <span class="text-red-500 font-bold" title="Ce champ est obligatoire">*</span>
            @endif
        </label>
    @endif

    <!-- Zone de texte dynamique -->
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
        @input="resize()"
    >{{ old($name, $slot) }}</textarea>

    <!-- Pied de zone : Messages d'aide, d'erreurs et compteur -->
    <div class="flex justify-between items-start gap-4 min-h-[20px] px-1 select-none">
        <div class="flex-1">
            @if($helper)
                <p class="text-xs text-gray-500 italic">{{ $helper }}</p>
            @endif

            @error($name)
                <p class="text-xs font-medium text-red-600 mt-0.5">{{ $message }}</p>
            @enderror
        </div>

        <!-- Compteur de caractères dynamique (Affiché uniquement si maxlength est défini) -->
        @if($maxlength)
            <span
                class="text-xs font-mono font-medium transition-colors"
                :class="value.length >= {{ $maxlength }} * 0.9 ? 'text-red-500 font-bold' : 'text-gray-400'"
                x-text="value.length + ' / {{ $maxlength }}'"
            ></span>
        @endif
    </div>
</div>
