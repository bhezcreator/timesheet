@props([
    'tabs' => [],
    'active' => ''
])

<div x-data="{ activeTab: '{{ $active }}' }" class="w-full">
    <!-- Navigation -->
    <div class="border-b border-gray-200">
        <nav class="flex gap-6" aria-label="Tabs">
            @foreach($tabs as $tab)
                <button
                    type="button"
                    @click="activeTab = '{{ $tab['key'] }}'"
                    class="py-3 text-sm font-medium transition border-b-2 focus:outline-none cursor-pointer"
                    :class="activeTab === '{{ $tab['key'] }}' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                >
                    @if(!empty($tab['icon']))
                        <i class="{{ $tab['icon'] }} mr-1.5"></i>
                    @endif

                    <span>{{ $tab['label'] }}</span>
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Contenu -->
    <div class="mt-6">
        {{ $slot }}
    </div>
</div>
