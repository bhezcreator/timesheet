@if ($paginator->hasPages())
    <nav class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 shadow-sm" aria-label="Pagination">
        <!-- Version Mobile -->
        <div class="flex justify-between items-center w-full sm:hidden gap-3">
            @if($paginator->onFirstPage())
                <span class="px-4 py-2 text-sm text-gray-400 border border-gray-200 cursor-pointer bg-gray-50 rounded-lg cursor-not-allowed select-none">
                    Précédent
                </span>
            @else
                <button type="button" wire:click="previousPage" wire:loading.attr="disabled" class="px-4 py-2 text-sm text-gray-700 cursor-pointer border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150">
                    Précédent
                </button>
            @endif

            @if($paginator->hasMorePages())
                <button type="button" wire:click="nextPage" wire:loading.attr="disabled" class="px-4 py-2 text-sm text-gray-700 cursor-pointer border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150">
                    Suivant
                </button>
            @else
                <span class="px-4 py-2 text-sm text-gray-400 cursor-pointer border border-gray-200 bg-gray-50 rounded-lg cursor-not-allowed select-none">
                    Suivant
                </span>
            @endif
        </div>

        <!-- Version Desktop -->
        <div class="hidden sm:flex sm:items-center sm:justify-between w-full">
            <!-- Informations de compteur -->
            <div class="text-sm text-gray-600">
                Affichage de
                <span class="font-semibold text-gray-900">{{ $paginator->firstItem() }}</span>
                à
                <span class="font-semibold text-gray-900">{{ $paginator->lastItem() }}</span>
                sur
                <span class="font-semibold text-gray-900">{{ $paginator->total() }}</span>
            </div>

            <!-- Liste des numéros de pages avec double boucle de sécurité -->
            <div class="flex items-center gap-1">
                @php
                    // Extraction brute et sécurisée de la structure générée par l'usine Laravel
                    $elements = $paginator->render()->offsetGet('elements') ?? [];
                @endphp

                @foreach ($elements as $element)
                    {{-- Cas 1 : C'est le séparateur "Three Dots" (...) --}}
                    @if (is_string($element))
                        <span class="px-3 py-2 text-sm text-gray-400 select-none">{{ $element }}</span>
                    @endif

                    {{-- Cas 2 : C'est un tableau de liens (Numéros de pages) --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="px-3 py-2 text-sm font-semibold cursor-pointer rounded-lg bg-blue-600 text-white shadow-sm select-none">
                                    {{ $page }}
                                </span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" class="px-3 py-2 text-sm text-gray-700 cursor-pointer border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
    </nav>
@endif
