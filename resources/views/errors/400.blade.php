@extends('layouts.errors')
@section('title', 'Requête invalide')
@section('description')

    <div class="max-w-xl w-full px-6 text-center">

        <!-- Illustration -->
        <div class="flex justify-center mb-8">
            <div class="bg-blue-100 rounded-full p-8">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-24 h-24 text-blue-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M9.75 9.75h.008v.008H9.75V9.75zm4.5 0h.008v.008h-.008V9.75zM8.25 14.25c.75.75 1.75 1.125 3.75 1.125s3-.375 3.75-1.125M12 21a9 9 0 100-18 9 9 0 000 18z"
                    />
                </svg>
            </div>
        </div>


        <!-- Code erreur -->
        <h1 class="text-8xl font-extrabold text-gray-900 tracking-tight">
            400
        </h1>


        <!-- Message -->
        <h2 class="mt-4 text-3xl font-bold text-gray-800">
            Requête invalide
        </h2>

        <p class="mt-4 text-gray-500 text-lg">
            Désolé, la demande envoyée au serveur n'est pas valide.
            Vérifiez les informations fournies puis réessayez.
        </p>


        <!-- Actions -->
        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

            <a href="{{ url('/') }}"
                class="px-6 py-3 rounded-lg bg-blue-600 text-white font-semibold
                        hover:bg-blue-700 transition duration-300 shadow-md">
                Retour à l'accueil
            </a>


            <button onclick="history.back()"
                class="px-6 py-3 rounded-lg border border-gray-300
                        text-gray-700 font-semibold
                        hover:bg-gray-100 transition duration-300">
                Page précédente
            </button>

        </div>


        <!-- Footer -->
        <p class="mt-12 text-sm text-gray-400">
            Code erreur : HTTP 400 Bad Request
        </p>

    </div>

@endsection
