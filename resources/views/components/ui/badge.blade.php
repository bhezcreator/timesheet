@props([
    'variant' => $variant ?? 'default',
    'size' => $size ?? 'md',
])


@php

$variants = [

    'default' =>
        'bg-gray-100 text-gray-700',


    'primary' =>
        'bg-blue-100 text-blue-700',


    'success' =>
        'bg-green-100 text-green-700',


    'danger' =>
        'bg-red-100 text-red-700',


    'warning' =>
        'bg-yellow-100 text-yellow-700',


    'info' =>
        'bg-cyan-100 text-cyan-700',


    'purple' =>
        'bg-purple-100 text-purple-700',


];


$sizes = [

    'sm' =>
        'px-2 py-0.5 text-xs',


    'md' =>
        'px-3 py-1 text-sm',


    'lg' =>
        'px-4 py-1.5 text-base',

];

@endphp



<span

{{ $attributes->merge([

'class' => '

inline-flex
items-center
rounded-full
font-semibold
'.$variants[$variant].
' '.
$sizes[$size]

]) }}

>

    {{ $slot }}

</span>
