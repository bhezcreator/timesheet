<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TimeSheet - Gestion des feuilles de temps</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
    <body class="bg-gray-50 text-gray-900 antialiased">
        <!-- Navigation -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 text-white w-12 h-12 rounded-xl flex items-center justify-center">
                        <i class="las la-clock text-3xl"></i>
                    </div>

                    <div>
                        <h1 class="font-bold text-xl">
                            TimeSheet
                        </h1>

                        <p class="text-xs text-gray-500">
                            Gestion intelligente du temps
                        </p>
                    </div>
                </div>

                @if(Route::has('login'))
                    <nav class="flex items-center gap-5">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-indigo-600">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600">
                                <i class="las la-sign-in-alt"></i>
                                Connexion
                            </a>

                            @if(Route::has('register'))
                                <a href="{{ route('register') }}"
                                class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700">
                                    Créer un compte
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <!-- Hero -->
        <section class="max-w-7xl mx-auto px-6 py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-sm">
                        <i class="las la-bolt"></i>
                        Solution professionnelle
                    </span>

                    <h2 class="text-5xl font-bold mt-6 leading-tight">
                        Gérez vos feuilles de temps
                        <br>
                        <span class="text-indigo-600">
                            Simplement et efficacement
                        </span>
                    </h2>
                    <p class="mt-6 text-lg text-gray-600 leading-relaxed">
                        TimeSheet permet aux équipes de suivre leurs heures,
                        leurs projets et leurs activités en temps réel.
                        Une solution complète pour améliorer la productivité.
                    </p>
                </div>
                <!-- Dashboard preview -->

                <div class="bg-white rounded-3xl shadow-xl p-6 border">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold">
                            Résumé semaine
                        </h3>
                        <span class="text-green-600 text-sm">
                            <i class="las la-check-circle"></i>
                            Synchronisé
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-indigo-50 p-5 rounded-xl">
                            <i class="las la-business-time text-3xl text-indigo-600"></i>
                            <p class="text-gray-500 mt-3">
                                Heures travaillées
                            </p>
                            <h4 class="text-3xl font-bold">
                                36h
                            </h4>
                        </div>

                        <div class="bg-green-50 p-5 rounded-xl">
                            <i class="las la-project-diagram text-3xl text-green-600"></i>
                            <p class="text-gray-500 mt-3">
                                Projets actifs
                            </p>
                            <h4 class="text-3xl font-bold">
                                8
                            </h4>
                        </div>
                    </div>

                    <div class="mt-6 bg-gray-50 rounded-xl p-5">
                        <div class="flex justify-between mb-3">
                            <span>
                                Développement
                            </span>

                            <span>
                                24h
                            </span>
                        </div>

                        <div class="h-3 bg-gray-200 rounded-full">
                            <div class="h-3 bg-indigo-600 rounded-full w-3/4">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="bg-white py-20">
            <div class="max-w-7xl mx-auto px-6">
                <h2 class="text-3xl font-bold text-center">
                    Tout ce qu'il faut pour gérer votre temps
                </h2>

                <p class="text-center text-gray-600 mt-3">
                    Une plateforme pensée pour les équipes modernes.
                </p>
                <div class="grid md:grid-cols-3 gap-8 mt-12">
                    <div class="p-8 rounded-2xl shadow-sm border">
                        <i class="las la-calendar-check text-5xl text-indigo-600"></i>
                        <h3 class="font-bold text-xl mt-5">
                            Suivi des heures
                        </h3>

                        <p class="text-gray-600 mt-3">
                            Enregistrez facilement les temps passés sur chaque activité.
                        </p>
                    </div>

                    <div class="p-8 rounded-2xl shadow-sm border">
                        <i class="las la-users text-5xl text-indigo-600"></i>
                        <h3 class="font-bold text-xl mt-5">
                            Gestion des équipes
                        </h3>

                        <p class="text-gray-600 mt-3">
                            Suivez les performances et la charge de travail.
                        </p>
                    </div>

                    <div class="p-8 rounded-2xl shadow-sm border">
                        <i class="las la-chart-line text-5xl text-indigo-600"></i>
                        <h3 class="font-bold text-xl mt-5">
                            Rapports avancés
                        </h3>

                        <p class="text-gray-600 mt-3">
                            Analysez vos données avec des tableaux de bord.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-8 text-center text-gray-500">
            © {{ date('Y') }} TimeSheet. Tous droits réservés.
        </footer>
    </body>
</html>
