<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

<!-- === 1. BLOC PATIENT === -->
<!-- Bouton d'ouverture Patient -->
<button
    type="button"
    data-open-modal="patient-modal"
    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded transition"
>
    + Ajouter patient
</button>

<!-- Modale Patient -->
<x-ui.modal-one id="patient-modal" title="Nouveau patient" size="lg">
    <form class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
            <input type="text" class="border border-gray-300 rounded p-2 w-full focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Nom du patient" />
        </div>
    </form>
</x-ui.modal-one>


<!-- === 2. BLOC SUPPRESSION === -->
<!-- Bouton d'ouverture Suppression -->
<button
    type="button"
    data-open-modal="delete-modal"
    class="bg-gray-100 hover:bg-red-50 text-gray-700 hover:text-red-600 font-medium px-4 py-2 rounded border border-gray-200 transition"
>
    Supprimer
</button>

<!-- Modale Suppression -->
<x-ui.modal-one id="delete-modal" title="Confirmation" size="sm">
    <p class="text-gray-600 text-sm">
        Voulez-vous vraiment supprimer cet élément ? Cette action est irréversible.
    </p>

    <x-slot name="footer">
        <button
            type="button"
            data-close-modal
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition"
        >
            Annuler
        </button>
        <button
            type="button"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded transition"
        >
            Supprimer
        </button>
    </x-slot>
</x-ui.modal-one>


</x-app-layout>
