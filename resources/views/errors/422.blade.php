@extends('layouts.errors')

@section('title', 'Données invalides')

@section('description')

<div class="max-w-xl w-full px-6 text-center">

    <!-- Illustration -->
    <div class="flex justify-center mb-8">
        <div class="bg-yellow-100 rounded-full p-8">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-24 h-24 text-yellow-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <!-- Document -->
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M6 3h8l4 4v14H6V3z" />

                <!-- Lignes du formulaire -->
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M9 11h6M9 15h4" />

                <!-- Symbole erreur -->
                <circle
                    cx="17"
                    cy="17"
                    r="3"
                    stroke-width="1.5" />

                <path
                    stroke-linecap="round"
                    stroke-width="1.5"
                    d="M17 15.8v1.8" />

                <circle
                    cx="17"
                    cy="18.8"
                    r=".4"
                    fill="currentColor"
                    stroke="none" />

            </svg>
        </div>
    </div>

    <!-- Code erreur -->
    <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
        422
    </h1>

    <!-- Message -->
    <h2 class="mt-4 text-3xl font-bold text-gray-800">
        Données invalides
    </h2>

    <p class="mt-4 text-lg text-gray-500">
        Les informations envoyées ne peuvent pas être traitées.
        Vérifiez les données saisies puis corrigez les erreurs avant de réessayer.
    </p>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

        <button
            onclick="history.back()"
            class="px-6 py-3 rounded-lg bg-yellow-600 text-white font-semibold hover:bg-yellow-700 transition duration-300 shadow-md">
            Corriger les informations
        </button>

        <a href="{{ url('/') }}"
            class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition duration-300">
            Retour à l'accueil
        </a>

    </div>

    <!-- Footer -->
    <p class="mt-12 text-sm text-gray-400">
        Code erreur : HTTP 422 Unprocessable Content
    </p>

</div>

@endsection
