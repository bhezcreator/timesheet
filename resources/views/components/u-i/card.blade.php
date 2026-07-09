@props([
    'title' => null,
    'icon' => null,
    'color' => 'blue',
])


@php

$colors = [

    'blue' =>
        'bg-blue-50 text-blue-600',

    'green' =>
        'bg-green-50 text-green-600',

    'red' =>
        'bg-red-50 text-red-600',

    'yellow' =>
        'bg-yellow-50 text-yellow-600',

    'purple' =>
        'bg-purple-50 text-purple-600',

    'gray' =>
        'bg-gray-50 text-gray-600',

];

@endphp



<div
    {{ $attributes->merge([

        'class'=>'
        bg-white
        rounded-2xl
        border
        border-gray-100
        shadow-sm
        hover:shadow-md
        transition
        duration-300
        '

    ]) }}

>


    <!-- Header -->

    @if($title || $icon)

    <div
        class="
        flex
        items-center
        justify-between
        px-6
        pt-6
        "
    >


        <h3
            class="
            font-semibold
            text-gray-800
            "
        >

            {{ $title }}

        </h3>



        @if($icon)

        <div
            class="
            w-10
            h-10
            rounded-xl
            flex
            items-center
            justify-center
            {{ $colors[$color] }}
            "
        >

            <i class="{{ $icon }} text-xl"></i>

        </div>

        @endif


    </div>

    @endif



    <!-- Body -->

    <div class="p-6">

        {{ $slot }}

    </div>



    <!-- Footer -->

    @isset($footer)

    <div
        class="
        px-6
        py-4
        border-t
        bg-gray-50
        rounded-b-2xl
        "
    >

        {{ $footer }}

    </div>

    @endisset



</div>
