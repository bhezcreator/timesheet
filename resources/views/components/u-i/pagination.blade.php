@if ($paginator->hasPages())


<nav
    class="
    flex
    items-center
    justify-between
    "
>


    <!-- Mobile -->

    <div class="flex justify-between sm:hidden">


        @if($paginator->onFirstPage())

            <span
            class="
            px-4
            py-2
            text-sm
            text-gray-400
            border
            rounded-lg
            "
            >
                Précédent
            </span>


        @else

            <a
            href="{{ $paginator->previousPageUrl() }}"
            class="
            px-4
            py-2
            text-sm
            border
            rounded-lg
            hover:bg-gray-100
            "
            >
                Précédent
            </a>

        @endif



        @if($paginator->hasMorePages())

            <a
            href="{{ $paginator->nextPageUrl() }}"
            class="
            px-4
            py-2
            text-sm
            border
            rounded-lg
            hover:bg-gray-100
            "
            >

                Suivant

            </a>


        @endif


    </div>




    <!-- Desktop -->

    <div
        class="
        hidden
        sm:flex
        items-center
        justify-between
        w-full
        "
    >



        <div class="text-sm text-gray-500">

            Affichage de

            <span class="font-semibold">

                {{ $paginator->firstItem() }}

            </span>


            à

            <span class="font-semibold">

                {{ $paginator->lastItem() }}

            </span>


            sur

            <span class="font-semibold">

                {{ $paginator->total() }}

            </span>


        </div>





        <div class="flex gap-1">


            @foreach($paginator->links()->elements[0] ?? [] as $page => $url)


                @if($page == $paginator->currentPage())


                    <span
                    class="
                    px-3
                    py-2
                    text-sm
                    rounded-lg
                    bg-blue-600
                    text-white
                    "
                    >

                    {{ $page }}

                    </span>


                @else


                    <a

                    href="{{ $url }}"

                    class="
                    px-3
                    py-2
                    text-sm
                    rounded-lg
                    border
                    hover:bg-gray-100
                    "
                    >

                    {{ $page }}

                    </a>


                @endif


            @endforeach



        </div>



    </div>


</nav>


@endif
