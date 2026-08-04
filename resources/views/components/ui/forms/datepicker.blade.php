@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => 'Sélectionner une date',
    'min' => null,
    'max' => null,
    'disabled' => false,
    'helper' => null,
])

<div x-data="{ value: '{{ old($name, $value) }}' }" class="space-y-2">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700">
            {{ $label }}
        </label>
    @endif

    <div>
        <input
            {{ $attributes }}
            type="date"
            id="{{ $name }}"
            name="{{ $name }}"
            x-model="value"
            min="{{ $min }}"
            max="{{ $max }}"
            placeholder="{{ $placeholder }}"
            @disabled($disabled)
            class="w-full rounded-xl border px-4 py-3 text-gray-700 shadow-sm transition bg-white focus:ring-4 @error($name) border-red-500 focus:border-red-500 focus:ring-red-100 @else border-gray-300 focus:border-blue-500 focus:ring-blue-100 @enderror"
        >
    </div>

    @if($helper)
        <p class="text-sm text-gray-500">
            {{ $helper }}
        </p>
    @endif

    @error($name)
        <p class="text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>
