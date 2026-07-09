<div x-data="{ open: false }" class="lg:hidden">


    <!-- Bouton Hamburger -->

    <button
        @click="open = true"
        class="
            p-3
            rounded-xl
            text-gray-600
            hover:bg-gray-100
            transition
            cursor-pointer
        "
    >

        <i class="las la-bars text-2xl"></i>

    </button>





    <!-- Overlay -->

    <div
        x-show="open"
        x-transition.opacity
        @click="open=false"
        class="
            fixed
            inset-0
            bg-black/40
            z-40
        "
    ></div>





    <!-- Menu Mobile -->

    <aside
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"

        class="
            fixed
            top-0
            left-0
            z-50
            w-72
            h-screen
            bg-white
            shadow-xl
            flex
            flex-col
        "
    >


        <!-- Header Menu -->

        <div class="
            h-20
            flex
            items-center
            justify-between
            px-6
        ">


            <div class="flex items-center gap-3">


                <div class="
                    w-10
                    h-10
                    rounded-xl
                    bg-gradient-to-r
                    from-indigo-600
                    to-purple-600
                    flex
                    items-center
                    justify-center
                    text-white
                ">

                    <i class="las la-clock text-2xl"></i>

                </div>


                <span class="font-bold text-xl">
                    TimeSheet
                </span>


            </div>





            <!-- Close -->

            <button
                @click="open=false"
                class="cursor-pointer text-gray-500 hover:text-red-500"
            >

                <i class="las la-times text-2xl"></i>

            </button>


        </div>







        <!-- Navigation -->

        <div class="
            flex-1
            overflow-y-auto
            p-4
            space-y-2
        ">


            @foreach($menus as $menu)


                @php
                    $active = $menu['route'] !== '#'
                        && request()->routeIs($menu['route']);
                @endphp



                <a
                    href="{{ $menu['route'] === '#' ? '#' : route($menu['route']) }}"

                    @if($menu['route'] !== '#')
                        wire:navigate
                    @endif

                    @click="open=false"


                    class="
                        flex
                        items-center
                        gap-3
                        px-4
                        py-3
                        rounded-xl
                        transition
                        duration-200

                        {{ $active
                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30'
                            : 'text-gray-700 hover:bg-gray-100'
                        }}
                    "
                >


                    <i class="las {{ $menu['icon'] }} text-xl"></i>


                    <span>
                        {{ $menu['title'] }}
                    </span>


                </a>


            @endforeach


        </div>







        <!-- Logout -->

        <div class="mt-auto pt-3 border-t border-gray-200">
            <button
                wire:click="logout"
                @click="open=false"

                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition cursor-pointer">

                <i class="las la-sign-out-alt text-xl"></i>
                Déconnexion
            </button>
        </div>
    </aside>
</div>
