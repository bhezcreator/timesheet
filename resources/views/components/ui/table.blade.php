@props([
    'columns' => []
])

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-1">
    <!-- Header du bloc (Optionnel) -->
    @isset($header)
        <div class="px-6 py-4 border-b border-gray-100 bg-white">
            {{ $header }}
        </div>
    @endisset

    <!-- Zone de défilement horizontal -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <!-- En-tête du Tableau -->
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    @foreach($columns as $column)
                        <th
                            scope="col"
                            @class([
                                'px-6 py-4 font-semibold uppercase text-xs tracking-wider',
                                'text-gray-600',
                                'text-left' => ! $loop->last,
                                'text-right' => $loop->last,
                            ])
                        >
                            {{ $column }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <!-- Corps du Tableau -->
            <tbody class="divide-y divide-gray-100">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
