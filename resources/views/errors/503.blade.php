@extends('layouts.errors')

@section('title', 'Service indisponible')

@section('description')

<div class="max-w-xl w-full px-6 text-center">

    <!-- Illustration -->
    <div class="flex justify-center mb-8">
        <div class="bg-gray-100 rounded-full p-8">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-24 h-24 text-gray-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <!-- Serveur -->
                <rect
                    x="5"
                    y="4"
                    width="14"
                    height="6"
                    rx="1.5"
                    stroke-width="1.5" />

                <rect
                    x="5"
                    y="14"
                    width="14"
                    height="6"
                    rx="1.5"
                    stroke-width="1.5" />

                <!-- Voyants -->
                <circle cx="8" cy="7" r=".6" fill="currentColor" stroke="none"/>
                <circle cx="8" cy="17" r=".6" fill="currentColor" stroke="none"/>

                <!-- Clé à molette -->
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M15.5 10.5a2.5 2.5 0 01-3.2-3.2l-4.8 4.8a2 2 0 102.8 2.8l4.8-4.8a2.5 2.5 0 003.2 3.2l1.2-1.2-4-4 1.2-1.2z"/>

            </svg>
        </div>
    </div>

    <!-- Code erreur -->
    <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
        503
    </h1>

    <!-- Message -->
    <h2 class="mt-4 text-3xl font-bold text-gray-800">
        Service temporairement indisponible
    </h2>

    <p class="mt-4 text-lg text-gray-500">
        Le service est actuellement indisponible en raison d'une maintenance
        ou d'une surcharge temporaire.
        Veuillez patienter quelques instants avant de réessayer.
    </p>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

        <a href="{{ url('/') }}"
            class="px-6 py-3 rounded-lg bg-gray-700 text-white font-semibold hover:bg-gray-800 transition duration-300 shadow-md">
            Retour à l'accueil
        </a>

        <button
            onclick="location.reload()"
            class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition duration-300">
            Réessayer
        </button>

    </div>

    <!-- Footer -->
    <p class="mt-12 text-sm text-gray-400">
        Code erreur : HTTP 503 Service Unavailable
    </p>

</div>

@endsection
