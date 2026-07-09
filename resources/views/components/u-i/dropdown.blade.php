@props([
    'trigger'
])


@php

$positions = [

    'left' =>
        'left-0',

    'right' =>
        'right-0',
];
@endphp

<div
    x-data="{open:false}"
    @click.outside="open=false"
    class="relative inline-block text-left"
>

    <!-- Trigger -->
    <div
        @click="open=!open"
    >
        {{ $trigger }}
    </div>

    <!-- Menu -->
    <div
        x-show="open"

        x-transition

        class="
        absolute
        mt-2
        w-48
        rounded-xl
        shadow-lg
        bg-white
        border
        border-gray-100
        z-50
        {{ $positions[$position] }}
        "

        style="display:none"

    >

        <div class="py-2">
            {{ $slot }}
        </div>
    </div>
</div>
