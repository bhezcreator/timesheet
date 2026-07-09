@extends('layouts.errors')

@section('title', 'Erreur interne du serveur')

@section('description')

<div class="max-w-xl w-full px-6 text-center">

    <!-- Illustration -->
    <div class="flex justify-center mb-8">
        <div class="bg-rose-100 rounded-full p-8">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-24 h-24 text-rose-600"
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

                <!-- Ligne -->
                <path
                    stroke-linecap="round"
                    stroke-width="1.5"
                    d="M11 7h5M11 17h5" />

            </svg>
        </div>
    </div>

    <!-- Code erreur -->
    <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
        500
    </h1>

    <!-- Message -->
    <h2 class="mt-4 text-3xl font-bold text-gray-800">
        Erreur interne du serveur
    </h2>

    <p class="mt-4 text-lg text-gray-500">
        Une erreur inattendue est survenue lors du traitement de votre demande.
        Notre équipe a été informée et travaille à résoudre le problème.
        Veuillez réessayer dans quelques instants.
    </p>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

        <a href="{{ url('/') }}"
            class="px-6 py-3 rounded-lg bg-rose-600 text-white font-semibold hover:bg-rose-700 transition duration-300 shadow-md">
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
        Code erreur : HTTP 500 Internal Server Error
    </p>

</div>

@endsection
