<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>
        {{ config('app.name', 'TimeSheet') }}
    </title>


    <!-- Fonts -->

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
          rel="stylesheet" />


    <!-- Assets -->

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])


</head>



<body class="antialiased">


<div class="min-h-screen bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-700 flex items-center justify-center px-6">


    <!-- Background decoration -->

    <div class="absolute inset-0 overflow-hidden">


        <div class="absolute -top-40 -right-40 w-96 h-96 bg-white/10 rounded-full blur-3xl">
        </div>


        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white/10 rounded-full blur-3xl">
        </div>


    </div>





    <div class="relative w-full max-w-md">



        <!-- Logo -->


        <div class="text-center mb-8">


            <a href="/" wire:navigate class="inline-flex flex-col items-center">


                <div class="w-20 h-20 bg-white rounded-2xl shadow-xl flex items-center justify-center">


                    <i class="las la-clock text-5xl text-indigo-600"></i>


                </div>



                <h1 class="mt-4 text-3xl font-bold text-white">

                    TimeSheet

                </h1>



                <p class="text-indigo-100 mt-1">

                    Gestion intelligente des feuilles de temps

                </p>


            </a>


        </div>






        <!-- Card -->


        <div class="bg-white/95 backdrop-blur rounded-3xl shadow-2xl px-8 py-8">



            {{ $slot }}



        </div>





        <!-- Footer -->


        <p class="text-center text-sm text-white/80 mt-8">


            © {{ date('Y') }} TimeSheet.
            Tous droits réservés.


        </p>
    </div>

</div>



</body>


</html>
