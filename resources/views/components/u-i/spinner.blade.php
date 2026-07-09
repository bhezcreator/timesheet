@props([
    'size' => $size ?? 'md',
    'color' => $color ?? 'blue',
])


@php

$sizes = [

    'sm' => 'w-4 h-4 border-2',

    'md' => 'w-6 h-6 border-2',

    'lg' => 'w-10 h-10 border-4',

    'xl' => 'w-14 h-14 border-4',

];


$colors = [

    'blue' => 'border-blue-600',

    'green' => 'border-green-600',

    'red' => 'border-red-600',

    'gray' => 'border-gray-500',

    'white' => 'border-white',

];

@endphp



<div

{{ $attributes->merge([

'class'=>'

animate-spin

rounded-full

border-t-transparent

'.$sizes[$size].

' '.

$colors[$color]

]) }}

>

</div>
