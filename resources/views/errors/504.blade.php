@extends('layouts.errors')

@section('title', 'Délai d\'attente dépassé')

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

                <!-- Horloge -->
                <circle
                    cx="12"
                    cy="12"
                    r="9"
                    stroke-width="1.5" />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M12 7v5l3 2" />

                <!-- Point d'exclamation -->
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M18 5l2 2" />

            </svg>
        </div>
    </div>

    <!-- Code erreur -->
    <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
        504
    </h1>

    <!-- Message -->
    <h2 class="mt-4 text-3xl font-bold text-gray-800">
        Délai d'attente dépassé
    </h2>

    <p class="mt-4 text-lg text-gray-500">
        Le serveur n'a pas reçu de réponse dans le délai imparti.
        Cette situation est généralement temporaire. Veuillez patienter quelques instants avant de réessayer.
    </p>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

        <a href="{{ url('/') }}"
            class="px-6 py-3 rounded-lg bg-yellow-600 text-white font-semibold hover:bg-yellow-700 transition duration-300 shadow-md">
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
        Code erreur : HTTP 504 Gateway Timeout
    </p>

</div>

@endsection
