@props([
    'type' => 'info',
    'dismissible' => true,
])

@php
    $styles = [
        'success' => [
            'box' => 'bg-green-50 border-green-200 text-green-800',
            'icon' => 'las la-check-circle text-green-600',
        ],
        'error' => [
            'box' => 'bg-red-50 border-red-200 text-red-800',
            'icon' => 'las la-times-circle text-red-600',
        ],
        'danger' => [ // Ajout de la clé de secours pour éviter les plantages
            'box' => 'bg-red-50 border-red-200 text-red-800',
            'icon' => 'las la-times-circle text-red-600',
        ],
        'warning' => [
            'box' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'icon' => 'las la-exclamation-triangle text-yellow-600',
        ],
        'info' => [
            'box' => 'bg-blue-50 border-blue-200 text-blue-800',
            'icon' => 'las la-info-circle text-blue-600',
        ],
    ];

    // Sécurité supplémentaire : Si le type demandé n'existe pas, on applique 'info' par défaut
    $currentStyle = $styles[$type] ?? $styles['info'];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition
    class="flex items-start gap-3 p-4 rounded-xl border {{ $currentStyle['box'] }}"
>
    <!-- Icone -->
    <div class="pt-0.5">
        <i class="{{ $currentStyle['icon'] }} text-xl"></i>
    </div>

    <!-- Message -->
    <div class="flex-1 text-sm font-medium">
        {{ $slot }}
    </div>

    <!-- Bouton de fermeture -->
    @if($dismissible)
        <button
            type="button"
            x-on:click="show = false"
            class="text-gray-400 hover:text-gray-700 transition cursor-pointer focus:outline-none"
        >
            <i class="las la-times"></i>
        </button>
    @endif
</div>
