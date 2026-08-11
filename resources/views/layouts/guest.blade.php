<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'TimeSheet') }} - CNRSC ASBL
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
          rel="stylesheet" />
<link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">

    <!-- Assets -->
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        /* Couleurs CNRSC */
        :root {
            --cnrsc-green: #0077d8;
            --cnrsc-green-dark: #0a5a9e;
            --cnrsc-green-light: #e6f0fa;
            --cnrsc-orange: #e67e22;
            --cnrsc-orange-light: #fdf0e8;
        }

        .shadow-custom {
            box-shadow: 0 20px 60px -15px rgba(0, 119, 216, 0.2);
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

<body class="antialiased bg-gray-50">
        <!-- ===== LOADING OVERLAY ===== -->
    <div id="loader" class="loader-overlay">
        <!-- Logo -->
        <div class="loader-logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="CNRSC ASBL">
        </div>

        <!-- Spinner -->
        <div class="loader-spinner"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center px-6 bg-gray-50">

        <!-- Décoration de fond discrète -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-96 h-96 opacity-10"
                 style="background: radial-gradient(circle, var(--cnrsc-green), transparent 70%);">
            </div>
            <div class="absolute bottom-0 left-0 w-96 h-96 opacity-10"
                 style="background: radial-gradient(circle, var(--cnrsc-orange), transparent 70%);">
            </div>
        </div>

        <div class="relative w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="/" wire:navigate class="inline-flex flex-col items-center group">
                    <div class="w-24 h-24 rounded-2xl shadow-lg flex items-center justify-center overflow-hidden p-1 transition-transform duration-300 group-hover:scale-105"
                         style="background: linear-gradient(135deg, var(--cnrsc-green), var(--cnrsc-orange));">
                        <div class="w-full h-full rounded-xl bg-white flex items-center justify-center">
                            <img src="{{ asset('images/logo.jpg') }}"
                                 alt="Logo CNRSC"
                                 class="w-16 h-16 object-contain">
                        </div>
                    </div>

                    <h1 class="mt-4 text-3xl font-bold"
                        style="color: var(--cnrsc-green);">
                        CNRSC ASBL
                    </h1>

                    <p class="mt-1 text-sm font-medium"
                       style="color: var(--cnrsc-orange);">
                        Gestion intelligente des feuilles de temps
                    </p>
                </a>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-3xl shadow-custom px-8 py-8 border border-gray-100">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <p class="text-center text-sm text-gray-400 mt-8">
                © {{ date('Y') }}
                <span class="font-semibold" style="color: var(--cnrsc-green);">CNRSC ASBL</span>
                - Tous droits réservés.
            </p>
        </div>
    </div>

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
