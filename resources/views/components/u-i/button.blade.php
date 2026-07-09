@props([
    'variant' => $variant ?? 'primary',
    'size' => $size ?? 'md',
    'type' => 'button'
])


@php

$variants = [

    'primary' =>
        'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',

    'secondary' =>
        'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-400',

    'success' =>
        'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',

    'danger' =>
        'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',

    'warning' =>
        'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-400',

    'outline' =>
        'border border-gray-300 text-gray-700 hover:bg-gray-100 focus:ring-gray-400',

];


$sizes = [

    'sm' =>
        'px-3 py-2 text-sm',

    'md' =>
        'px-5 py-2.5 text-sm',

    'lg' =>
        'px-6 py-3 text-base',

];

@endphp


<button
    type="{{ $type }}"

    {{ $attributes->merge([

        'class' =>
        'inline-flex items-center justify-center gap-2
        rounded-lg font-semibold
        transition-all duration-200
        focus:outline-none focus:ring-2 focus:ring-offset-2
        disabled:opacity-50 disabled:cursor-not-allowed
        shadow-sm'
        .' '.$variants[$variant]
        .' '.$sizes[$size]

    ]) }}

>

    {{ $slot }}

</button>
