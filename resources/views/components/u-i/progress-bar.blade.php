@props([
    'value' => $value ?? 0,
    'color' => $color ?? 'blue',
    'size' => $size ?? 'md',
])


@php

$colors = [

    'blue' =>
        'bg-blue-600',

    'green' =>
        'bg-green-600',

    'red' =>
        'bg-red-600',

    'yellow' =>
        'bg-yellow-500',

    'purple' =>
        'bg-purple-600',

];


$sizes = [

    'sm' =>
        'h-1.5',

    'md' =>
        'h-3',

    'lg' =>
        'h-5',

];

@endphp



<div class="w-full">


    <!-- Barre externe -->

    <div
        class="
        w-full
        bg-gray-200
        rounded-full
        overflow-hidden
        {{ $sizes[$size] }}
        "
    >


        <!-- Progression -->

        <div

            class="
            h-full
            rounded-full
            transition-all
            duration-500
            {{ $colors[$color] }}
            "

            style="width: {{ $value }}%"

        >

        </div>


    </div>


</div>
