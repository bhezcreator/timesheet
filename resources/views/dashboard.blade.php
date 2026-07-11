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


<x-ui.tabs :tabs="[
    [
        'key'   => 'resume',
        'label' => 'Résumé',
        'icon'  => 'las la-home'
    ],
    [
        'key'   => 'tasks',
        'label' => 'Tâches',
        'icon'  => 'las la-tasks'
    ],
    [
        'key'   => 'time',
        'label' => 'Temps',
        'icon'  => 'las la-clock'
    ]
]" active="resume"> <!-- Ajoutez l'attribut active par défaut ici -->

    <!-- CONTENU DES ONGLETS -->

    <!-- Contenu de l'onglet Résumé -->
    <div x-show="activeTab === 'resume'">
        <p class="text-gray-600">Voici le contenu du résumé de votre projet ou activité.</p>
    </div>

    <!-- Contenu de l'onglet Tâches -->
    <div x-show="activeTab === 'tasks'" x-cloak>
        <p class="text-gray-600">Liste de vos tâches en cours et terminées.</p>
    </div>

    <!-- Contenu de l'onglet Temps -->
    <div x-show="activeTab === 'time'" x-cloak>
        <p class="text-gray-600">Suivi du temps passé sur ce projet.</p>
    </div>

</x-ui.tabs>

</x-app-layout>
