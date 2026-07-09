@extends('layouts.errors')

@section('title', 'Méthode non autorisée')

@section('description')

<div class="max-w-xl w-full px-6 text-center">

    <!-- Illustration -->
    <div class="flex justify-center mb-8">
        <div class="bg-indigo-100 rounded-full p-8">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-24 h-24 text-indigo-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <!-- Cercle -->
                <circle
                    cx="12"
                    cy="12"
                    r="9"
                    stroke-width="1.5" />

                <!-- Main d'interdiction -->
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M9 9l6 6m0-6l-6 6" />

            </svg>
        </div>
    </div>

    <!-- Code erreur -->
    <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
        405
    </h1>

    <!-- Message -->
    <h2 class="mt-4 text-3xl font-bold text-gray-800">
        Méthode non autorisée
    </h2>

    <p class="mt-4 text-lg text-gray-500">
        La méthode HTTP utilisée pour accéder à cette ressource n'est pas autorisée.
        Veuillez vérifier votre requête ou retourner à la page précédente.
    </p>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

        <a href="{{ url('/') }}"
            class="px-6 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition duration-300 shadow-md">
            Retour à l'accueil
        </a>

        <button
            onclick="history.back()"
            class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition duration-300">
            Page précédente
        </button>

    </div>

    <!-- Footer -->
    <p class="mt-12 text-sm text-gray-400">
        Code erreur : HTTP 405 Method Not Allowed
    </p>

</div>

@endsection
