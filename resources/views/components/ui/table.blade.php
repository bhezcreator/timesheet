<div class="w-full min-w-0 max-w-full p-3 box-border bg-white rounded-2xl border border-gray-100 border-t-2 border-t-blue-700 shadow-sm overflow-hidden mt-1">

    <!-- Header du bloc -->
    @isset($header)
        <div class="pb-4 border-b border-gray-100 bg-white">
            {{ $header }}
        </div>
    @endisset

    <!-- Zone de défilement horizontal -->
    <div class="w-full overflow-x-auto">

        <table class="min-w-full text-sm text-left">

            <!-- En-tête du tableau -->
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    @foreach($columns as $column)
                        <th
                            scope="col"
                            @class([
                                'px-4 sm:px-6 py-4 font-semibold uppercase text-xs tracking-wider whitespace-nowrap',
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

            <!-- Corps du tableau -->
            <tbody class="divide-y divide-gray-100">
                {{ $slot }}
            </tbody>

        </table>

    </div>
</div>