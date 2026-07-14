@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => null,
    'required' => false
])

<div class="space-y-2">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-500">
            {{ $label }}
            @if($required)
                <span class="text-red-500 font-bold" title="Ce champ est obligatoire">*</span>
            @endif
        </label>
    @endif

    <input
        {{ $attributes->merge([
            'type'        => $type,
            'name'        => $name,
            'id'          => $name,
            'placeholder' => $placeholder,
            'class'       => 'w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-200 focus:outline-none'
        ]) }}
        @required($required)
    />
</div>
