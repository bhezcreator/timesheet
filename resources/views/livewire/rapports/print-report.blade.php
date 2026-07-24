<div class="p-4 space-y-6">
    <div class="flex items-center gap-2 text-gray-600 text-sm print:hidden ">
        <i class="las la-info-circle text-lg text-blue-500"></i>
        <span>Ajustez vos options de mise en page (activer les graphismes d'arrière-plan) avant de valider.</span>
    </div>

    <!-- Zone d'actions (Masquée lors de l'impression) -->
    <div class="print:hidden flex justify-between items-center bg-white p-4 rounded-xl shadow-xs border-t border-t-blue-700">
        <button type="button"
                @click="window.history.back()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-gray-50 text-sm font-semibold text-gray-700 hover:text-blue-600 border border-gray-200 rounded-xl shadow-3xs transition duration-150 group cursor-pointer">
            <i class="las la-arrow-left text-base text-gray-400 group-hover:text-blue-500 transition-colors"></i>
            <span>Retour</span>
        </button>

        <button type="button"
                onclick="window.print()"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer">
            <i class="las la-print text-base"></i> Imprimer le document
        </button>
    </div>

    <!-- BLOC À IMPRIMER -->
    <div class="bg-white p-4 rounded-2xl shadow-xs border border-gray-100 print:border-none print:shadow-none print:p-0 space-y-4">

        <!-- En-tête : Logo & Titre Général -->
        <div class="flex justify-between items-start border-b border-gray-200 pb-6">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest block">Document Officiel</span>
                <h1 class="text-xl font-black text-gray-900 uppercase">Feuille de Temps</h1>
                <p class="text-xs text-gray-500 font-medium">{{ $report->full_title }}</p>
            </div>
            <!-- Emplacement du Logo de l'entreprise -->
            <div class="shrink-0">
                <img src="/images/logo-entreprise.png" alt="Logo Entreprise" class="h-12 w-auto object-contain fallback-logo" onerror="this.style.display='none'; document.getElementById('text-logo').style.display='block'">
                <div id="text-logo" class="hidden text-right font-black text-lg text-gray-800 tracking-tight">ENTERPRISE LOGO</div>
            </div>
        </div>

        <!-- Informations Utilisateur & Métadonnées -->
        <div class="grid grid-cols-2 gap-6 bg-gray-50 p-4 rounded-xl text-xs print:bg-gray-50/50">
            <div class="space-y-1.5">
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Collaborateur</span>
                <div class="font-bold text-gray-800 text-sm">{{ $report->user->name }}</div>
                <div class="text-gray-500">{{ $report->user->email }}</div>
            </div>
            <div class="space-y-1.5 text-right">
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Détails du document</span>
                <div>Date d'édition : <span class="font-semibold text-gray-700">{{ $report->report_date->translatedFormat('d F Y') }}</span></div>
                <div>Statut : <span class="font-bold text-green-600 uppercase">{{ $report->status }}</span></div>
            </div>
        </div>

        <!-- SECTION TABLEAU : CONTENU DE LA FEUILLE DE TEMPS -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-1.5 border-b border-gray-100 pb-2">
                <i class="las la-list-alt text-base text-blue-500"></i> Informations détaillées des activités
            </h3>

            @if(!$isMultiProject)
                <!-- PRÉSENTATION STANDARDS : RAPPORT MONO-PROJET -->
                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 font-bold uppercase text-[10px] tracking-wider">
                                <th class="p-3">Journée</th>
                                <th class="p-3">Type Activité</th>
                                <th class="p-3">Description</th>
                                <th class="p-3 text-center">Début</th>
                                <th class="p-3 text-center">Fin</th>
                                <th class="p-3 text-right">Total Journée</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-600">
                            @foreach($activities as $activity)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="p-3 font-semibold text-gray-800 whitespace-nowrap">{{ $activity->activity_date->translatedFormat('d F Y') }}</td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $activity->activityType->color ?? '#cbd5e1' }}"></span>
                                            {{ $activity->activityType->name }}
                                        </span>
                                    </td>
                                    <td class="p-3 max-w-xs truncate" title="{{ $activity->description }}">{{ $activity->titre }}</td>
                                    <td class="p-3 text-center whitespace-nowrap">{{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }}</td>
                                    <td class="p-3 text-center whitespace-nowrap">{{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}</td>
                                    <td class="p-3 text-right font-bold text-gray-800">{{ number_format($activity->duration, 0) }}h</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-bold border-t border-gray-200 text-gray-800">
                                <td colspan="5" class="p-3 text-right uppercase tracking-wider text-[10px]">Volume Horaire Total Cumulé :</td>
                                <td class="p-3 text-right text-sm font-black text-blue-600 bg-blue-50/50">{{ number_format($totalReportHours, 2) }}h</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <!-- PRÉSENTATION CROISÉE : RAPPORT MULTI-PROJETS -->
                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 font-bold uppercase text-[10px] tracking-wider">
                                <th class="p-3">Journée</th>
                                @foreach($projectsList as $pId => $pName)
                                    <th class="p-3 text-center border-l border-gray-200">{{ $pName }}</th>
                                @endforeach
                                <th class="p-3 text-right border-l border-gray-200 bg-gray-50">Total Journée</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-600">
                            @foreach($matrix as $dayStr => $data)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="p-3 font-semibold text-gray-800 whitespace-nowrap">{{ $data['date_formatted'] }}</td>
                                    @foreach($projectsList as $pId => $pName)
                                        <td class="p-3 text-center border-l border-gray-200 font-medium">
                                            {{ isset($data['projects'][$pId]) ? number_format($data['projects'][$pId], 0) . 'h' : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="p-3 text-right font-bold text-gray-900 border-l border-gray-200 bg-gray-50/40">
                                        {{ number_format($data['total_day'], 0) }}h
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-bold border-t border-gray-200 text-gray-800">
                                <td class="p-3 uppercase tracking-wider text-[10px]">Totaux par Projet :</td>
                                @foreach($projectsList as $pId => $pName)
                                    <td class="p-3 text-center border-l border-gray-200 text-gray-700">
                                        {{ number_format($activities->where('project_id', $pId)->sum('duration'), 2) }}h
                                    </td>
                                @endforeach
                                <td class="p-3 text-right text-sm font-black text-blue-600 border-l border-gray-200 bg-blue-50/50">
                                    {{ number_format($totalReportHours, 2) }}h
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

        <!-- RUBRIQUES RÉDACTIONNELLES DU RAPPORT (OBJECTIFS, RÉALISATIONS, ETAPES SUIVANTES) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-gray-100 break-inside-avoid">
            <div class="bg-gray-50/40 border border-gray-200/60 p-4 rounded-xl space-y-2">
                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1">
                    <i class="las la-bullseye text-sm"></i> Objectifs assignés
                </span>
                <p class="text-xs text-gray-600 whitespace-pre-line leading-relaxed">{{ $report->objectives ?? 'Aucun renseigné.' }}</p>
            </div>

            <div class="bg-gray-50/40 border border-gray-200/60 p-4 rounded-xl space-y-2">
                <span class="text-[10px] font-bold text-green-600 uppercase tracking-wider flex items-center gap-1">
                    <i class="las la-check-circle text-sm"></i> Réalisations validées
                </span>
                <p class="text-xs text-gray-600 whitespace-pre-line leading-relaxed">{{ $report->achievements ?? 'Aucune renseignée.' }}</p>
            </div>

            <div class="bg-gray-50/40 border border-gray-200/60 p-4 rounded-xl space-y-2">
                <span class="text-[10px] font-bold text-purple-600 uppercase tracking-wider flex items-center gap-1">
                    <i class="las la-rocket text-sm"></i> Actions futures
                </span>
                <p class="text-xs text-gray-600 whitespace-pre-line leading-relaxed">{{ $report->next_actions ?? 'Aucune planifiée.' }}</p>
            </div>
        </div>

        <!-- BLOC LÉGAL, AUTHENTIFICATION & SIGNATURE -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-gray-200 break-inside-avoid">
            <!-- Déclaration légale d'authenticité -->
            <div class="space-y-2 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Clause d'authentification</span>
                <p class="text-[11px] text-gray-500 leading-relaxed italic">
                    Je soussigné, certifie sur l'honneur l'exactitude des informations fournies ci-dessus reflétant fidèlement l'état d'avancement ainsi que le décompte des heures allouées aux activités des projets concernés pour la période déclarée.
                </p>
            </div>

            <!-- Signature électronique du déclarant -->
            <div class="flex flex-col items-end justify-center space-y-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block text-right w-full">Signature du déclarant</span>
                <div class="h-24 w-48 border border-gray-200 bg-gray-50/30 rounded-xl p-2 flex items-center justify-center relative overflow-hidden">
                    @if($report->user && $report->user->signature)
                        <img src="{{ $report->user->signature }}" alt="Signature Électronique" class="max-h-full max-w-full object-contain mix-blend-multiply">
                    @else
                        <span class="text-[10px] text-gray-300 italic">Aucune signature numérisée</span>
                    @endif
                </div>
                <span class="text-[10px] font-medium text-gray-400">Signé électroniquement par {{ $report->user->name ?? 'N/A' }}</span>
            </div>
        </div>

    </div>
</div>
