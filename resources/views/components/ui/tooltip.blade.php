@props([
    'text' => $text,
    'position' => $position ?? 'top'
])


@php

$positions = [

    'top' =>
        'bottom-full left-1/2 -translate-x-1/2 mb-2',

    'bottom' =>
        'top-full left-1/2 -translate-x-1/2 mt-2',

    'left' =>
        'right-full top-1/2 -translate-y-1/2 mr-2',

    'right' =>
        'left-full top-1/2 -translate-y-1/2 ml-2',

];

@endphp



<div
    x-data="{show:false}"

    @mouseenter="show=true"

    @mouseleave="show=false"

    class="
    relative
    inline-flex
    "
>


    <!-- Element principal -->

    {{ $slot }}



    <!-- Tooltip -->

    <div

        x-show="show"

        x-transition

        class="
        absolute
        z-50
        whitespace-nowrap
        px-3
        py-2
        rounded-lg
        text-xs
        font-medium
        text-white
        bg-gray-900
        shadow-lg
        {{ $positions[$position] }}
        "

        style="display:none"

    >

        {{ $text }}
    </div>
</div>
