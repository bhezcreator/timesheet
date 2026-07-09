@extends('layouts.errors')

@section('title', 'Page introuvable')

@section('description')

<div class="max-w-xl w-full px-6 text-center">

    <!-- Illustration -->
    <div class="flex justify-center mb-8">
        <div class="bg-amber-100 rounded-full p-8">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-24 h-24 text-amber-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <!-- Loupe -->
                <circle
                    cx="11"
                    cy="11"
                    r="6"
                    stroke-width="1.5" />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M20 20l-4.2-4.2" />

                <!-- Point d'interrogation -->
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M11 8.5a1.8 1.8 0 013.1 1.3c0 1.4-1.6 1.8-2.2 2.8-.2.3-.3.6-.3.9" />

                <circle
                    cx="11"
                    cy="16.5"
                    r=".5"
                    fill="currentColor"
                    stroke="none" />

            </svg>
        </div>
    </div>

    <!-- Code erreur -->
    <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
        404
    </h1>

    <!-- Message -->
    <h2 class="mt-4 text-3xl font-bold text-gray-800">
        Page introuvable
    </h2>

    <p class="mt-4 text-lg text-gray-500">
        La page que vous recherchez n'existe pas, a été déplacée ou son adresse est incorrecte.
        Vérifiez l'URL ou retournez à la page d'accueil.
    </p>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

        <a href="{{ url('/') }}"
            class="px-6 py-3 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 transition duration-300 shadow-md">
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
        Code erreur : HTTP 404 Not Found
    </p>

</div>

@endsection
