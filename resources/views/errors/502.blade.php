@extends('layouts.errors')

@section('title', 'Passerelle incorrecte')

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

                <!-- Deux serveurs -->
                <rect
                    x="2.5"
                    y="6"
                    width="6"
                    height="12"
                    rx="1.5"
                    stroke-width="1.5" />

                <rect
                    x="15.5"
                    y="6"
                    width="6"
                    height="12"
                    rx="1.5"
                    stroke-width="1.5" />

                <!-- Connexion interrompue -->
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M8.5 12h2m3 0h2" />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M12 10l-1.5 2L12 14l1.5-2L12 10z" />

            </svg>
        </div>
    </div>

    <!-- Code erreur -->
    <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
        502
    </h1>

    <!-- Message -->
    <h2 class="mt-4 text-3xl font-bold text-gray-800">
        Passerelle incorrecte
    </h2>

    <p class="mt-4 text-lg text-gray-500">
        Le serveur a reçu une réponse invalide d'un autre serveur.
        Ce problème est généralement temporaire. Veuillez réessayer dans quelques instants.
    </p>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

        <a href="{{ url('/') }}"
            class="px-6 py-3 rounded-lg bg-orange-600 text-white font-semibold hover:bg-orange-700 transition duration-300 shadow-md">
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
        Code erreur : HTTP 502 Bad Gateway
    </p>

</div>

@endsection
