@extends('layouts.errors')

@section('title', 'Accès interdit')

@section('description')

<div class="max-w-xl w-full px-6 text-center">

    <!-- Illustration -->
    <div class="flex justify-center mb-8">
        <div class="bg-red-100 rounded-full p-8">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-24 h-24 text-red-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728"/>

                <circle
                    cx="12"
                    cy="12"
                    r="9"
                    stroke-width="1.5" />

            </svg>
        </div>
    </div>

    <!-- Code erreur -->
    <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
        403
    </h1>

    <!-- Message -->
    <h2 class="mt-4 text-3xl font-bold text-gray-800">
        Accès interdit
    </h2>

    <p class="mt-4 text-lg text-gray-500">
        Vous n'avez pas les autorisations nécessaires pour accéder à cette
        ressource. Si vous pensez qu'il s'agit d'une erreur, contactez
        l'administrateur.
    </p>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

        <a href="{{ url('/') }}"
            class="px-6 py-3 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition duration-300 shadow-md">
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
        Code erreur : HTTP 403 Forbidden
    </p>

</div>

@endsection
