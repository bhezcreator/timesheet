<div class="p-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Paramètres
        </h2>
    </x-slot>
    {{-- En-tête de la page --}}
    <div>
        <h1 class="text-2xl font-700 text-gray-900 tracking-tight">Paramètres</h1>
        <p class="text-sm text-gray-500 mt-0.5">Gérez vos informations personnelles, la sécurité de vos accès et vos préférences.</p>
    </div>

    {{-- Système d'onglets --}}
    <div class="bg-white border border-gray-200 my-4">

        {{-- Version Mobile : Menu déroulant (S'affiche uniquement sur les petits écrans) --}}
        <div class="sm:hidden mb-4">
            <label for="tabs" class="sr-only">Choisir un onglet</label>
            <select
                id="tabs"
                wire:model.live="activeTab"
                class="block w-full rounded-2xl border-gray-300 text-base focus:border-indigo-500 focus:ring-indigo-500 py-2.5 shadow-xs"
            >
                @foreach($tabs as $key => $tab)
                    <option value="{{ $key }}">{{ $tab['label'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- Version Bureau : Onglets alignés horizontaux (Masqué sur mobile) --}}
        <div class="hidden sm:block">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                @foreach($tabs as $key => $tab)
                    <button
                        type="button"
                        wire:click="changeTab('{{ $key }}')"
                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all cursor-pointer whitespace-nowrap gap-2
                        {{ $activeTab === $key
                            ? 'border-indigo-600 text-indigo-600 font-semibold'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        }}"
                    >
                        <i class="las {{ $tab['icon'] }} text-lg transition-colors
                            {{ $activeTab === $key ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}
                        "></i>
                        <span>{{ $tab['label'] }}</span>
                    </button>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Conteneur dynamique de contenu --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-xs min-h-[300px] transition-all duration-200">

        {{-- Contenu : Profil --}}
        @if($activeTab === 'profile')
            <div class="space-y-2 animate-fade-in" wire:key="tab-content-profile">
                <p class="text-sm text-gray-700">Mettez à jour vos coordonnées publiques et votre avatar d'identification.</p>

                {{-- Insérez votre composant ou formulaire de profil ici --}}
                @livewire('users.profil')
            </div>
        @endif

        {{-- Contenu : Sécurité --}}
        @if($activeTab === 'capture')
            <div class="space-y-4 animate-fade-in" wire:key="tab-content-security">
                @livewire('users.signature-capture')
            </div>
        @endif

                {{-- Contenu : Type activité --}}
        @if($activeTab === 'type')
            <div class="space-y-4 animate-fade-in" wire:key="tab-content-security">
                @livewire('activity-types.index')
            </div>
        @endif

                {{-- Contenu : Paramètres généraux --}}
        @if($activeTab === 'general')
            <div class="space-y-4 animate-fade-in" wire:key="tab-content-general">
                <h2 class="text-lg font-bold text-gray-900">Paramètres généraux</h2>
                <p class="text-sm text-gray-500">Configurez les options système et d'administration de votre espace de travail.</p>
                {{-- Insérez vos formulaires généraux ici --}}
            </div>
        @endif
    </div>
</div>
