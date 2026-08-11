<div class="p-4 space-y-6">
    <!-- Message d'information avant impression -->
    <div class="flex items-center gap-2 text-gray-600 text-sm print:hidden">
        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Avant impression : assurez-vous d'activer les graphismes d'arrière-plan pour inclure tous les éléments visuels.</span>
    </div>

    <!-- Zone d'actions (Masquée lors de l'impression) -->
    <div class="print:hidden flex flex-col sm:flex-row justify-between items-center gap-3 bg-white p-4 rounded-xl shadow-sm border-t-2 border-t-blue-600">
        <button type="button"
                onclick="window.history.back()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-gray-50 text-sm font-semibold text-gray-700 hover:text-blue-600 border border-gray-200 rounded-xl transition duration-150 group cursor-pointer">
            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour</span>
        </button>

        <button type="button"
                onclick="window.print()"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition shadow-sm flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimer le document
        </button>
    </div>

    <!-- BLOC À IMPRIMER -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 print:border-none print:shadow-none print:p-0 space-y-6">

        <!-- En-tête : Logo & Titre Général -->
        <div class="flex justify-between items-start border-b border-gray-200 pb-6">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest block">Document Officiel</span>
                <h1 class="text-2xl font-black text-gray-900 uppercase">Liste des Rapports</h1>
                <p class="text-xs text-gray-500 font-medium">Généré le {{ now()->translatedFormat('d F Y à H:i') }}</p>
            </div>

            <!-- Emplacement du Logo CNRSC -->
            <div class="shrink-0 flex flex-col items-center gap-1">
                <img src="{{ asset('images/logo.jpg') }}"
                    alt="Logo CNRSC ASBL"
                    class="h-16 w-auto object-contain rounded-xl shadow-md border-2 border-blue-600 fallback-logo"
                    onerror="this.style.display='none'; document.getElementById('text-logo').style.display='flex'">
                <div id="text-logo" class="hidden items-center gap-2 font-bold text-lg tracking-tight">
                    <span class="text-blue-600">CNRSC</span>
                    <span class="text-orange-500">ASBL</span>
                </div>
                <div class="flex items-center gap-1 font-bold text-sm tracking-tight">
                    <span class="text-blue-600">CNRSC</span>
                    <span class="text-orange-500">ASBL</span>
                </div>
            </div>
        </div>

        <!-- Informations des filtres -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl text-xs print:bg-gray-50/50">
            @if($filters['status'] ?? false)
                <div class="space-y-1">
                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Statut</span>
                    <div class="font-semibold text-gray-800">{{ ucfirst($filters['status']) }}</div>
                </div>
            @endif
            @if(($filters['month'] ?? false) && ($filters['year'] ?? false))
                <div class="space-y-1">
                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Période</span>
                    <div class="font-semibold text-gray-800">
                        {{ Carbon\Carbon::createFromDate($filters['year'], $filters['month'], 1)->translatedFormat('F Y') }}
                    </div>
                </div>
            @endif
            @if($filters['search'] ?? false)
                <div class="space-y-1">
                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Recherche</span>
                    <div class="font-semibold text-gray-800">"{{ $filters['search'] }}"</div>
                </div>
            @endif
            <div class="space-y-1">
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Total</span>
                <div class="font-semibold text-gray-800">{{ $statistics['total'] ?? 0 }} rapports</div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-3 sm:grid-cols-3 gap-4">
            <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-xl text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $statistics['soumis'] ?? 0 }}</div>
                <div class="text-xs font-medium text-gray-600 mt-1">Soumis</div>
            </div>
            <div class="bg-emerald-50/50 border border-emerald-100 p-4 rounded-xl text-center">
                <div class="text-2xl font-bold text-emerald-600">{{ $statistics['approuves'] ?? 0 }}</div>
                <div class="text-xs font-medium text-gray-600 mt-1">Approuvés</div>
            </div>
            <div class="bg-red-50/50 border border-red-100 p-4 rounded-xl text-center">
                <div class="text-2xl font-bold text-red-600">{{ $statistics['rejetes'] ?? 0 }}</div>
                <div class="text-xs font-medium text-gray-600 mt-1">Rejetés</div>
            </div>
        </div>

        <!-- SECTION TABLEAU -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-1.5 border-b border-gray-100 pb-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
                Détail des rapports
            </h3>

            @if($reports->isEmpty())
                <div class="text-center py-12 text-gray-500 bg-gray-50 rounded-xl">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-lg font-medium">Aucun rapport trouvé</p>
                    <p class="text-sm">Ajustez vos filtres pour voir plus de résultats</p>
                </div>
            @else
                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 font-bold uppercase text-[10px] tracking-wider">
                                <th class="p-3">#</th>
                                <th class="p-3">Utilisateur</th>
                                <th class="p-3">Période</th>
                                <th class="p-3">Projet(s)</th>
                                <th class="p-3">Statut</th>
                                <th class="p-3 text-center">Activités</th>
                                <th class="p-3">Date soumission</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-600">
                            @foreach($reports as $index => $report)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="p-3 font-semibold text-gray-800">{{ $loop->iteration }}</td>
                                    <td class="p-3">
                                        <div class="font-semibold text-gray-800">{{ $report->user->name ?? 'N/A' }} {{ $report->user->first_name ?? 'N/A' }}</div>
                                        <div class="text-gray-500 text-[10px]">{{ $report->user->email ?? '' }}</div>
                                    </td>
                                    <td class="p-3">
                                        {{ Carbon\Carbon::createFromDate($report->year, $report->month, 1)->translatedFormat('F Y') }}
                                    </td>
                                    <td class="p-3">
                                        @php
                                            $projectIds = $this->normalizeProjectIds($report->project_ids);
                                            $count = count($projectIds);
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $count === 0 ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-700' }}">
                                            {{ $count === 0 ? 'Tous' : $count . ' projet(s)' }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        @php
                                            $statusColors = [
                                                'brouillon' => 'bg-gray-100 text-gray-700',
                                                'soumis' => 'bg-blue-100 text-blue-700',
                                                'approuvé' => 'bg-emerald-100 text-emerald-700',
                                                'rejeté' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$report->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-700 font-bold text-xs">
                                            {{ $report->activities->count() }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-500">
                                        {{ $report->submitted_at ? Carbon\Carbon::parse($report->submitted_at)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-bold border-t border-gray-200 text-gray-800">
                                <td colspan="7" class="p-3 text-right uppercase tracking-wider text-[10px]">
                                    Total Général : <span class="text-sm font-black text-blue-600">{{ $reports->count() }} rapport(s)</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

        <!-- Déclaration légale -->
        <div class="space-y-2 pt-4">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Clause d'authentification</span>
            <p class="text-[11px] text-gray-500 leading-relaxed italic">
                Ce document est généré automatiquement par le système de gestion des rapports.
                Les informations présentées sont extraites de la base de données et reflètent l'état
                des rapports à la date d'édition du document.
            </p>
        </div>

                <!-- BLOC LÉGAL, AUTHENTIFICATION & SIGNATURE -->
        <div class=" pt-6 border-t border-gray-200 break-inside-avoid">
            <!-- Signature électronique du déclarant -->
            <div class="flex flex-col items-end justify-center space-y-2">
                <span class="text-[10px] font-bold text-center text-gray-400 uppercase tracking-wider block">Signature du déclarant</span>
                <div class="h-24 w-48 border border-gray-200 bg-gray-50/30 rounded-xl p-2 flex items-center justify-center relative overflow-hidden">
                    @php
                        $user = Auth::user();
                    @endphp
                    @if($user->signature && Storage::disk('public')->exists($user->signature))
                        <img src="{{ Storage::url($user->signature) }}" alt="Signature Électronique" class="max-h-full max-w-full object-contain mix-blend-multiply">
                    @else
                        <span class="text-[10px] text-gray-300 italic">Aucune signature numérisée</span>
                    @endif
                </div>
                <span class="text-[12px] text-center font-medium text-gray-700">Signé électroniquement par  <br>
                    <span class="font-bold text-[14px]">{{ $user->name.' '.$user->first_name ?? 'N/A' }}</span> <br>
                    <span class="font-bold">{{ $user->job_title }}</span>
                </span>
            </div>
        </div>
    </div>
</div>
