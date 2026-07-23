<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Impression Rapport') }}</title>

    <!-- Intégration de Line Awesome pour les icônes -->
    <link rel="stylesheet" href="https://icons8.com">

    <!-- Chargement de vos assets (Vite / Tailwind) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Styles CSS spécifiques pour optimiser l'impression papier / PDF -->
   <style>
    @media print {
        /* 1. Masquer les en-têtes et pieds de page natifs du navigateur (Date, URL, Titre) */
        @page {
            margin: 0; /* Supprime complètement la zone des métadonnées du navigateur */
            size: auto;
        }

        /* 2. Recréer des marges intérieures pour que le contenu ne colle pas aux bords de la feuille */
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            background-color: #ffffff !important;
            padding: 20mm 15mm 20mm 15mm; /* Marges physiques : Haut, Droite, Bas, Gauche */
        }

        /* Évite de couper des éléments importants en milieu de page */
        .break-inside-avoid {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        /* Cache complètement l'arrière-plan global de l'application pendant l'impression */
        .bg-gray-50 {
            background-color: transparent !important;
        }
    }

    /* Amélioration visuelle pour la scrollbar si le tableau déborde sur l'écran avant impression */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
</style>

</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen printable-content">

    <!-- Contenu dynamique du composant Livewire -->
    <main>
        {{ $slot }}
    </main>
 @livewireScripts
</body>
</html>
