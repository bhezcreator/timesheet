@props([
    'title' => '',
])


@php

$sizes = [

    'sm' => 'max-w-md',

    'md' => 'max-w-lg',

    'lg' => 'max-w-2xl',

    'xl' => 'max-w-4xl',

];

@endphp



<div
    x-data="{ open:false }"

    x-on:open-modal.window="
        if($event.detail === '{{ $id }}')
            open=true
    "

    x-on:close-modal.window="
        if($event.detail === '{{ $id }}')
            open=false
    "

    x-show="open"

    class="fixed inset-0 z-50 overflow-y-auto"

    style="display:none"
>


    <!-- Overlay -->
    <div
        class="fixed inset-0 bg-black/50 backdrop-blur-sm"

        x-on:click="open=false"
    ></div>



    <!-- Container -->
    <div
        class="relative min-h-screen flex items-center justify-center p-4"
    >


        <!-- Modal -->
        <div
            x-show="open"

            x-transition

            class="
            relative
            w-full
            {{ $sizes[$size] }}

            bg-white
            rounded-2xl
            shadow-xl
            overflow-hidden
            "
        >


            <!-- Header -->
            <div
                class="
                flex
                items-center
                justify-between
                px-6
                py-4
                border-b
                "
            >

                <h3
                    class="text-lg font-bold text-gray-800"
                >
                    {{ $title }}
                </h3>



                <button

                    type="button"

                    x-on:click="open=false"

                    class="
                    text-gray-400
                    hover:text-gray-700
                    transition
                    "
                >

                    <i class="las la-times text-xl"></i>

                </button>


            </div>




            <!-- Body -->
            <div class="p-6">

                {{ $slot }}

            </div>



            <!-- Footer option -->
            @isset($footer)

            <div
                class="
                px-6
                py-4
                border-t
                bg-gray-50
                "
            >

                {{ $footer }}

            </div>

            @endisset



        </div>

    </div>


</div>
