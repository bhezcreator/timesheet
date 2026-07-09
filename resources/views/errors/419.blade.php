@extends('layouts.errors')

@section('title', 'Session expirée')

@section('description')

<div class="max-w-xl w-full px-6 text-center">

    <!-- Illustration -->
    <div class="flex justify-center mb-8">
        <div class="bg-purple-100 rounded-full p-8">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-24 h-24 text-purple-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <!-- Document -->
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M6 3h9l3 3v15H6V3z" />

                <!-- Horloge -->
                <circle
                    cx="15"
                    cy="15"
                    r="4"
                    stroke-width="1.5" />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M15 13v2l1.5 1" />

            </svg>
        </div>
    </div>

    <!-- Code erreur -->
    <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
        419
    </h1>

    <!-- Message -->
    <h2 class="mt-4 text-3xl font-bold text-gray-800">
        Session expirée
    </h2>

    <p class="mt-4 text-lg text-gray-500">
        Votre session a expiré pour des raisons de sécurité.
        Veuillez actualiser la page et réessayer votre opération.
    </p>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

        <button
            onclick="location.reload()"
            class="px-6 py-3 rounded-lg bg-purple-600 text-white font-semibold hover:bg-purple-700 transition duration-300 shadow-md">
            Actualiser la page
        </button>

        <a href="{{ url('/') }}"
            class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition duration-300">
            Retour à l'accueil
        </a>

    </div>

    <!-- Footer -->
    <p class="mt-12 text-sm text-gray-400">
        Code erreur : HTTP 419 Page Expired
    </p>

</div>

@endsection
