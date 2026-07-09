@extends('layouts.errors')

@section('title', 'Trop de requêtes')

@section('description')

<div class="max-w-xl w-full px-6 text-center">

    <!-- Illustration -->
    <div class="flex justify-center mb-8">
        <div class="bg-orange-100 rounded-full p-8">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-24 h-24 text-orange-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <!-- Serveur -->
                <rect
                    x="5"
                    y="4"
                    width="14"
                    height="16"
                    rx="2"
                    stroke-width="1.5" />

                <!-- Lignes serveur -->
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M8 8h8M8 12h8M8 16h5" />

                <!-- Symbole limitation -->
                <circle
                    cx="17"
                    cy="16"
                    r="3"
                    stroke-width="1.5" />

                <path
                    stroke-linecap="round"
                    stroke-width="1.5"
                    d="M17 14.8v1.8" />

                <circle
                    cx="17"
                    cy="18"
                    r=".4"
                    fill="currentColor"
                    stroke="none" />

            </svg>
        </div>
    </div>

    <!-- Code erreur -->
    <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
        429
    </h1>

    <!-- Message -->
    <h2 class="mt-4 text-3xl font-bold text-gray-800">
        Trop de requêtes
    </h2>

    <p class="mt-4 text-lg text-gray-500">
        Vous avez envoyé trop de requêtes en peu de temps.
        Pour protéger le service, votre accès est temporairement limité.
        Veuillez patienter quelques instants avant de réessayer.
    </p>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

        <button
            onclick="location.reload()"
            class="px-6 py-3 rounded-lg bg-orange-600 text-white font-semibold hover:bg-orange-700 transition duration-300 shadow-md">
            Réessayer
        </button>

        <a href="{{ url('/') }}"
            class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition duration-300">
            Retour à l'accueil
        </a>

    </div>

    <!-- Footer -->
    <p class="mt-12 text-sm text-gray-400">
        Code erreur : HTTP 429 Too Many Requests
    </p>

</div>

@endsection
