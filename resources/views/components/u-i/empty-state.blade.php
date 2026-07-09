@props([
    'title' => $title,
    'description' => $description,
    'icon' => $icon,
])


<div
    class="
    flex
    flex-col
    items-center
    justify-center
    text-center
    py-12
    px-6
    "
>


    <!-- Illustration -->

    <div
        class="
        w-20
        h-20
        rounded-full
        bg-gray-100
        flex
        items-center
        justify-center
        mb-5
        "
    >

        <i
            class="
            {{ $icon }}
            text-4xl
            text-gray-400
            "
        ></i>


    </div>



    <!-- Title -->

    <h3
        class="
        text-xl
        font-bold
        text-gray-800
        "
    >

        {{ $title }}

    </h3>



    <!-- Description -->

    @if($description)

    <p
        class="
        mt-2
        text-gray-500
        max-w-md
        "
    >

        {{ $description }}

    </p>

    @endif



    <!-- Action -->

    @isset($action)

        <div class="mt-6">

            {{ $action }}

        </div>

    @endisset



</div>
