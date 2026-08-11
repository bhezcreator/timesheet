<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TimeSheet - Gestion des feuilles de temps</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Couleurs personnalisées basées sur le logo CNRSC */
        :root {
            --cnrsc-green: #0077d8;
            --cnrsc-green-light: #e8f5ed;
            --cnrsc-green-dark: #0f4a2a;
            --cnrsc-orange: #e67e22;
            --cnrsc-orange-light: #fdf0e8;
            --cnrsc-orange-dark: #c96b1e;
        }

        .bg-cnrsc-green {
            background-color: var(--cnrsc-green);
        }
        .bg-cnrsc-green-light {
            background-color: var(--cnrsc-green-light);
        }
        .text-cnrsc-green {
            color: var(--cnrsc-green);
        }
        .border-cnrsc-green {
            border-color: var(--cnrsc-green);
        }

        .bg-cnrsc-orange {
            background-color: var(--cnrsc-orange);
        }
        .bg-cnrsc-orange-light {
            background-color: var(--cnrsc-orange-light);
        }
        .text-cnrsc-orange {
            color: var(--cnrsc-orange);
        }
        .border-cnrsc-orange {
            border-color: var(--cnrsc-orange);
        }

        .hover\:bg-cnrsc-green-dark:hover {
            background-color: var(--cnrsc-green-dark);
        }
        .hover\:text-cnrsc-green:hover {
            color: var(--cnrsc-green);
        }

        /* Animation douce */
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* ===== LOADING SPINNER ===== */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.6s ease-out, visibility 0.6s ease-out;
        }

        .loader-overlay.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-logo {
            width: 100px;
            height: 100px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px -15px rgba(0, 119, 216, 0.3);
            animation: pulse-logo 1.5s ease-in-out infinite;
        }

        .loader-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @keyframes pulse-logo {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 20px 60px -15px rgba(0, 119, 216, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 25px 80px -10px rgba(230, 126, 34, 0.4);
            }
        }

        /* Spinner circulaire avec les couleurs CNRSC */
        .loader-spinner {
            width: 60px;
            height: 60px;
            margin-top: 24px;
            border-radius: 50%;
            border: 4px solid var(--cnrsc-green-light);
            border-top-color: var(--cnrsc-green);
            border-right-color: var(--cnrsc-orange);
            animation: spin 0.9s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loader-text {
            margin-top: 20px;
            font-size: 14px;
            font-weight: 500;
            color: var(--cnrsc-green);
            letter-spacing: 1px;
            animation: text-pulse 1.8s ease-in-out infinite;
        }

        @keyframes text-pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        .loader-dots {
            display: inline-flex;
            gap: 4px;
        }

        .loader-dots span {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--cnrsc-green);
            animation: dot-bounce 1.4s ease-in-out infinite both;
        }

        .loader-dots span:nth-child(1) { animation-delay: -0.32s; }
        .loader-dots span:nth-child(2) { animation-delay: -0.16s; }
        .loader-dots span:nth-child(3) { animation-delay: 0s; }

        @keyframes dot-bounce {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40% { transform: scale(1.2); opacity: 1; }
        }

        /* Barre de progression en bas */
        .loader-progress {
            width: 200px;
            height: 3px;
            margin-top: 20px;
            border-radius: 4px;
            background: var(--cnrsc-green-light);
            overflow: hidden;
        }

        .loader-progress-bar {
            height: 100%;
            width: 0%;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--cnrsc-green), var(--cnrsc-orange));
            animation: progress 2.5s ease-in-out forwards;
        }

        @keyframes progress {
            0% { width: 0%; }
            20% { width: 15%; }
            40% { width: 35%; }
            60% { width: 55%; }
            80% { width: 80%; }
            100% { width: 100%; }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900 antialiased">

    <!-- ===== LOADING OVERLAY ===== -->
    <div id="loader" class="loader-overlay">
        <!-- Logo -->
        <div class="loader-logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="CNRSC ASBL">
        </div>

        <!-- Spinner -->
        <div class="loader-spinner"></div>
    </div>

    <!-- Navigation -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <!-- Logo CNRSC -->
                <div class="w-14 h-14 rounded-xl overflow-hidden shadow-md">
                    <img src="{{ asset('images/logo.jpg') }}"
                        alt="Logo CNRSC"
                        class="w-full h-full object-cover">
                </div>

                <div>
                    <h1 class="font-bold text-xl" style="color: var(--cnrsc-green);">
                        CNRSC ASBL
                    </h1>
                    <p class="text-xs" style="color: var(--cnrsc-orange);">
                        Gestion intelligente du temps
                    </p>
                </div>
            </div>

            @if(Route::has('login'))
                <nav class="flex items-center gap-5">
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-cnrsc-green transition-colors duration-200">
                            Tableau de bord
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-white px-5 py-2 rounded-lg transition-all duration-200 hover:shadow-lg" style="background: var(--cnrsc-orange);">
                            <i class="las la-sign-in-alt"></i>
                            Connexion
                        </a>
                    @endauth
                </nav>
            @endif
        </div>
    </header>

    <!-- Hero -->
    <section class="max-w-7xl mx-auto px-6 py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm"
                      style="background-color: var(--cnrsc-green-light); color: var(--cnrsc-green);">
                    <i class="las la-bolt"></i>
                    Solution professionnelle
                </span>

                <h2 class="text-5xl font-bold mt-6 leading-tight">
                    Gérez vos feuilles de temps
                    <br>
                    <span style="background: linear-gradient(135deg, var(--cnrsc-green), var(--cnrsc-orange));
                                 -webkit-background-clip: text;
                                 -webkit-text-fill-color: transparent;
                                 background-clip: text;">
                        Simplement et efficacement
                    </span>
                </h2>
                <p class="mt-6 text-lg text-gray-600 leading-relaxed">
                    La <strong class="text-cnrsc-green">CNRSC ASBL</strong> met à disposition de ses équipes
                    un outil de suivi du temps pour optimiser la gestion des projets communautaires.
                    TimeSheet facilite le reporting, renforce la transparence et améliore
                    l'efficacité des actions menées sur le terrain.
                </p>
            </div>
            <!-- Dashboard preview -->
            <div class="bg-white rounded-3xl shadow-xl p-6 border-1 animate-float"
                 style="border-color: var(--cnrsc-green);">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-cnrsc-green">
                        Résumé semaine
                    </h3>
                    <span class="text-sm" style="color: var(--cnrsc-green);">
                        <i class="las la-check-circle"></i>
                        Synchronisé
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-5 rounded-xl" style="background-color: var(--cnrsc-green-light);">
                        <i class="las la-business-time text-3xl" style="color: var(--cnrsc-green);"></i>
                        <p class="text-gray-500 mt-3">
                            Heures travaillées
                        </p>
                        <h4 class="text-3xl font-bold" style="color: var(--cnrsc-green);">
                            36h
                        </h4>
                    </div>

                    <div class="p-5 rounded-xl" style="background-color: var(--cnrsc-orange-light);">
                        <i class="las la-project-diagram text-3xl" style="color: var(--cnrsc-orange);"></i>
                        <p class="text-gray-500 mt-3">
                            Projets actifs
                        </p>
                        <h4 class="text-3xl font-bold" style="color: var(--cnrsc-orange);">
                            8
                        </h4>
                    </div>
                </div>

                <div class="mt-6 bg-gray-50 rounded-xl p-5">
                    <div class="flex justify-between mb-3">
                        <span class="text-cnrsc-green">
                            Développement
                        </span>
                        <span class="text-cnrsc-orange">
                            24h
                        </span>
                    </div>

                    <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-3 rounded-full"
                             style="background: linear-gradient(90deg, var(--cnrsc-green), var(--cnrsc-orange)); width: 75%;">
                        </div>
                    </div>
                </div>

                <!-- Badge CNRSC -->
                <div class="mt-4 flex justify-end">
                    <span class="text-xs font-bold px-3 py-1 rounded-full"
                          style="background: var(--cnrsc-green); color: white;">
                        CNRSC ASBL
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-20" style="background: linear-gradient(180deg, white, var(--cnrsc-green-light));">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-center">
                Tout ce qu'il faut pour gérer votre temps
            </h2>

            <p class="text-center mt-3" style="color: var(--cnrsc-orange);">
                Une plateforme pensée pour les équipes modernes.
            </p>

            <div class="grid md:grid-cols-3 gap-8 mt-12">
                <div class="p-8 rounded-2xl shadow-sm border-2 transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
                     style="border-color: var(--cnrsc-green);">
                    <i class="las la-calendar-check text-5xl" style="color: var(--cnrsc-green);"></i>
                    <h3 class="font-bold text-xl mt-5" style="color: var(--cnrsc-green);">
                        Suivi des heures
                    </h3>
                    <p class="text-gray-600 mt-3">
                        Enregistrez facilement les temps passés sur chaque activité.
                    </p>
                </div>

                <div class="p-8 rounded-2xl shadow-sm border-2 transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
                     style="border-color: var(--cnrsc-orange);">
                    <i class="las la-users text-5xl" style="color: var(--cnrsc-orange);"></i>
                    <h3 class="font-bold text-xl mt-5" style="color: var(--cnrsc-orange);">
                        Gestion des équipes
                    </h3>
                    <p class="text-gray-600 mt-3">
                        Suivez les performances et la charge de travail.
                    </p>
                </div>

                <div class="p-8 rounded-2xl shadow-sm border-2 transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
                     style="border-color: var(--cnrsc-green);">
                    <i class="las la-chart-line text-5xl" style="background: linear-gradient(135deg, var(--cnrsc-green), var(--cnrsc-orange));
                                 -webkit-background-clip: text;
                                 -webkit-text-fill-color: transparent;
                                 background-clip: text;"></i>
                    <h3 class="font-bold text-xl mt-5" style="color: var(--cnrsc-green);">
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
    <footer class="py-8 text-center"
            style="background: var(--cnrsc-green); color: white;">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col items-center gap-2">
                <span class="text-sm font-bold tracking-wider">
                    CNRSC ASBL
                </span>
                <span class="text-sm opacity-80">
                    Coordination Nationale de Renforcement du Système Communautaire
                </span>
                <div class="w-24 h-0.5 bg-white opacity-30 my-2"></div>
                <span class="text-sm opacity-60">
                    © {{ date('Y') }} TimeSheet. Tous droits réservés.
                </span>
            </div>
        </div>
    </footer>

    <!-- ===== SCRIPT POUR CACHER LE LOADING ===== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cacher le loader après le chargement complet
            setTimeout(function() {
                document.getElementById('loader').classList.add('hidden');
            }, 2500); // 2.5s (correspond à l'animation de la barre de progression)
        });

        // Cacher immédiatement si la page est déjà chargée
        if (document.readyState === 'complete') {
            document.getElementById('loader').classList.add('hidden');
        }
    </script>

</body>
</html>
