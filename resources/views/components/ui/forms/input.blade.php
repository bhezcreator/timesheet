@props([
    'label' => null,
    'name',
    'type' => 'text',
    'placeholder' => null
])

<div class="space-y-2">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
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
    />
</div>
