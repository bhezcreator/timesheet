@props([
    'items' => $items
])

<nav class="flex items-center text-sm mb-4 mt-0">
    <ol class="flex items-center flex-wrap gap-2">
        @foreach($items as $index => $item)
            <li class="flex items-center gap-2">
                @if(!$loop->last)
                    <a href="{{ $item['url'] ?? '#' }}" class="text-gray-500 hover:text-blue-600 transition">
                        {{ $item['label'] }}
                    </a>
                    <span class="text-gray-400">
                        <i class="las la-angle-right text-xs"></i>
                    </span>
                @else
                    <span class="font-semibold text-gray-800">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
