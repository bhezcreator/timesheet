<div class="py-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mes présences
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded-full p-3">
                        <i class="las la-calendar-check text-indigo-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-emerald-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Présent</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ $stats['present'] }}</p>
                    </div>
                    <div class="bg-emerald-50 rounded-full p-3">
                        <i class="las la-check-circle text-emerald-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-amber-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">En retard</p>
                        <p class="text-2xl font-bold text-amber-600">{{ $stats['late'] }}</p>
                    </div>
                    <div class="bg-amber-50 rounded-full p-3">
                        <i class="las la-clock text-amber-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-red-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Absent</p>
                        <p class="text-2xl font-bold text-red-600">{{ $stats['absent'] }}</p>
                    </div>
                    <div class="bg-red-50 rounded-full p-3">
                        <i class="las la-times-circle text-red-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Mode d'affichage -->
                <div class="flex gap-2">
                    <button wire:click="setFilterMode('month')" 
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                                {{ $filterMode === 'month' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <i class="las la-calendar-alt mr-1"></i> Mois
                    </button>
                    <button wire:click="setFilterMode('week')" 
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                                {{ $filterMode === 'week' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <i class="las la-calendar-week mr-1"></i> Semaine
                    </button>
                    <button wire:click="setFilterMode('day')" 
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                                {{ $filterMode === 'day' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <i class="las la-calendar-day mr-1"></i> Jour
                    </button>


                </div>

                <div class="flex-1"></div>

                <!-- Recherche -->
                <div class="relative w-full sm:w-56">
                    <i class="las la-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           wire:model.live="search" 
                           placeholder="Rechercher..."
                           class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                                    <!-- Version avec badge -->
                    <a href="{{ route('presente.scanner') }}" class="relative p-2 rounded-lg text-sm font-medium transition-colors bg-gray-100 text-gray-700 hover:bg-gray-200">
                        <i class="las la-qrcode"></i>
                        Scanner
                        <span class="absolute -top-1 -right-1 px-1.5 py-0.5 bg-red-500 text-white text-xs rounded-full">
                            !
                        </span>
                    </a>
            </div>
        </div>

        <!-- Calendrier -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="las la-calendar text-indigo-500"></i>
                        {{ $currentMonth }}
                    </h3>
                    <div class="flex gap-2">
                        <button wire:click="previousPeriod" 
                                class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="las la-chevron-left text-lg"></i>
                        </button>
                        <button wire:click="goToToday" 
                                class="px-3 py-1 text-sm bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors">
                            Aujourd'hui
                        </button>
                        <button wire:click="nextPeriod" 
                                class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="las la-chevron-right text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="grid grid-cols-7 gap-1">
                    <!-- Jours de la semaine -->
                    @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $day)
                        <div class="text-center text-sm font-medium text-gray-500 py-2">
                            {{ $day }}
                        </div>
                    @endforeach

                    <!-- Jours du mois -->
                    @php
                        $firstDay = Carbon\Carbon::parse($this->selectedDate)->startOfMonth()->dayOfWeek;
                        $daysInMonth = Carbon\Carbon::parse($this->selectedDate)->daysInMonth;
                        $startOffset = $firstDay == 0 ? 6 : $firstDay - 1;
                    @endphp

                    @for($i = 0; $i < $startOffset; $i++)
                        <div class="text-center py-3 text-gray-300"></div>
                    @endfor

                    @foreach($monthDays as $day)
                        <div wire:click="selectDate('{{ $day['date'] }}')" 
                             class="text-center py-3 rounded-lg cursor-pointer transition-all hover:scale-105
                                {{ $day['isToday'] ? 'bg-indigo-100 border-2 border-indigo-500' : '' }}
                                {{ $day['hasAttendance'] ? 'bg-emerald-50 hover:bg-emerald-100' : 'hover:bg-gray-50' }}
                                {{ $day['isWeekend'] ? 'text-gray-400' : 'text-gray-700' }}
                                {{ Carbon\Carbon::parse($day['date'])->format('Y-m-d') === $selectedDate ? 'ring-2 ring-indigo-400 ring-offset-2' : '' }}">
                            <span class="text-sm font-medium {{ $day['isToday'] ? 'text-indigo-700' : '' }}">
                                {{ $day['day'] }}
                            </span>
                            @if($day['hasAttendance'])
                                <div class="flex justify-center gap-0.5 mt-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    @if($day['attendanceCount'] > 1)
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    @endif
                                </div>
                                <span class="text-xs text-emerald-600 font-medium">
                                    {{ $day['attendanceCount'] }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Liste des présences -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="las la-list text-indigo-500"></i>
                    Détail des présences
                    <span class="text-sm font-normal text-gray-400">
                        ({{ $attendances->total() }})
                    </span>
                </h3>
                <button wire:click="exportCsv" 
                        class="px-4 py-2 text-sm bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-colors flex items-center gap-2">
                    <i class="las la-file-csv"></i>
                    Exporter CSV
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Heure</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bureau</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motif</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localisation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($attendances as $attendance)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $attendance->attendance_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $attendance->recorded_at->format('H:i:s') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $attendance->office?->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                        <i class="las {{ $this->getTypeIcon($attendance->type) }}"></i>
                                        {{ $this->getTypeLabel($attendance->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium 
                                        bg-{{ $this->getStatusColor($attendance->status) }}-50 
                                        text-{{ $this->getStatusColor($attendance->status) }}-700">
                                        <span class="w-1.5 h-1.5 rounded-full 
                                            bg-{{ $this->getStatusColor($attendance->status) }}-500"></span>
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-[150px] truncate">
                                    {{ $attendance->reason ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($attendance->latitude && $attendance->longitude)
                                        <span class="flex items-center gap-1">
                                            <i class="las la-map-pin text-indigo-400"></i>
                                            {{ number_format($attendance->latitude, 4) }},
                                            {{ number_format($attendance->longitude, 4) }}
                                            @if($attendance->distance)
                                                <span class="text-xs text-gray-400">
                                                    ({{ number_format($attendance->distance, 0) }}m)
                                                </span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="las la-calendar-plus text-4xl block mb-2 text-gray-300"></i>
                                    <p class="text-sm">Aucune présence enregistrée</p>
                                    <p class="text-xs text-gray-400 mt-1">Scannez un QR code pour commencer</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-100">
                <x-ui.pagination :paginator="$attendances" />
            </div>
        </div>
    </div>
</div>