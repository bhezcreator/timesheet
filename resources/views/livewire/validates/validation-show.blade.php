<div class="p-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Validation rapport.
        </h2>
    </x-slot>

    <!-- Fil d'Ariane & Bouton Retour -->
    <div class="flex items-center mb-4">
        <a href="{{ route('validates.supervisor') }}"
            wire:navigate class="font-semibold text-gray-500 inline-flex items-center gap-2 text-sm hover:text-indigo-600 transition-colors">
            <i class="las la-arrow-left text-base"></i> Retour à la liste
        </a>
    </div>

    <!-- En-tête Principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium shadow-sm
                    @if($report->status === 'En attente') bg-amber-50 text-amber-700 border border-amber-200
                    @elseif($report->status === 'Validé') bg-emerald-50 text-emerald-700 border border-emerald-200
                    @else bg-rose-50 text-rose-700 border border-rose-200 @endif">
                    ● Status : {{ $report->status }}
                </span>
                <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $report->fullTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Soumis par <span class="font-semibold text-gray-700">{{ $report->user->name }}</span> le {{ $report->submitted_at?->format('d/m/Y à H:i') ?? 'N/A' }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- COLONNE GAUCHE & CENTRE : CONTENU DU RAPPORT -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Sections Synthèse (Objectifs, Réalisations, Prochaines actions) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Synthèse Globale du Mois</h2>
                </div>
                <div class="p-6 space-y-6 divide-y divide-gray-100">
                    <div>
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Objectifs fixés</h3>
                        <div class="text-gray-700 prose prose-sm max-w-none">{!! nl2br(e($report->objectives)) !!}</div>
                    </div>
                    <div class="pt-6">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Réalisations majeures</h3>
                        <div class="text-gray-700 prose prose-sm max-w-none">{!! nl2br(e($report->achievements)) !!}</div>
                    </div>
                    <div class="pt-6">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Prochaines actions prévues</h3>
                        <div class="text-gray-700 prose prose-sm max-w-none">{!! nl2br(e($report->next_actions)) !!}</div>
                    </div>
                </div>
            </div>

            <!-- Liste des Activités Détaillées -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Lignes d'activités déclarées ({{ $report->activities->count() }})</h2>
                </div>

                @if($report->activities->isEmpty())
                    <div class="p-6 text-center text-gray-500 italic">Aucune activité détaillée enregistrée pour ce rapport.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description / Tâche</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($report->activities as $activity)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <p class="text-sm text-gray-900 font-medium">{{ $activity->description }}</p>
                                            @if($activity->rejection_reason)
                                                <p class="text-xs text-rose-600 mt-1 bg-rose-50 p-2 rounded border border-rose-100">
                                                    <strong>Motif rejet historique :</strong> {{ $activity->rejection_reason }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($activity->status === 'Validé') bg-emerald-100 text-emerald-800
                                                @elseif($activity->status === 'Rejeté') bg-rose-100 text-rose-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $activity->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- COLONNE DROITE : PIÈCES JOINTES & FORMULAIRE DECISION -->
        <div class="space-y-6">

            <!-- Zone Fichiers / Pièces jointes (Spatie Media Library) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    📎 Pièces Jointes
                </h2>

                @if($report->getMedia('attachments')->isEmpty())
                    <p class="text-sm text-gray-500 italic">Aucun document joint à ce rapport.</p>
                @else
                    <ul class="divide-y divide-gray-100 border border-gray-200 rounded-lg overflow-hidden">
                        @foreach($report->getMedia('attachments') as $media)
                            <li class="flex items-center justify-between p-3 hover:bg-gray-50 transition">
                                <div class="flex items-center space-x-3 truncate">
                                    <!-- Icône dynamique selon le type de fichier -->
                                    @if(str_contains($media->mime_type, 'pdf'))
                                        <span class="text-red-500 font-bold text-xs bg-red-50 p-2 rounded">PDF</span>
                                    @elseif(str_contains($media->mime_type, 'image'))
                                        <span class="text-blue-500 font-bold text-xs bg-blue-50 p-2 rounded">IMG</span>
                                    @else
                                        <span class="text-gray-500 font-bold text-xs bg-gray-50 p-2 rounded">DOC</span>
                                    @endif

                                    <div class="truncate">
                                        <p class="text-sm font-medium text-gray-800 truncate" title="{{ $media->file_name }}">
                                            {{ $media->file_name }}
                                        </p>
                                        <p class="text-xs text-gray-400">{{ $media->human_readable_size }}</p>
                                    </div>
                                </div>
                                <a href="{{ $media->getUrl() }}" target="_blank" class="ml-4 text-sm font-medium text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded transition">
                                    Ouvrir
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Bloc Décision / Validation du Superviseur -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Traitement</h2>
                @if($report->validation)
                        @if($report->validation->comment)
                            <p class="text-sm mt-2 italic bg-white bg-opacity-60 p-2 rounded text-gray-800">
                                "{{ $report->validation->comment }}"
                            </p>
                        @endif
                        <p class="text-xs text-gray-500 mt-3 pt-2 border-t border-black border-opacity-5">
                            Traité par : <span class="font-semibold text-gray-700">{{ $report->validation->validator->name }}</span> <br>
                            Le : {{ $report->validation->validated_at->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                @else
                    <!-- Formulaire Livewire Interactif -->
                    <form wire:submit.prevent="submitValidation" class="space-y-4">

                        <!-- Choix de l'action -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Votre Décision</label>

                            <div class="grid grid-cols-1 gap-2">
                                <label class="flex items-center justify-center p-3 border rounded-lg cursor-pointer transition select-none
                                    {{ $decision === 'Validé' ? 'bg-emerald-50 border-emerald-400 text-emerald-800 ring-2 ring-emerald-200' : 'bg-white border-gray-200 hover:bg-gray-50 text-gray-700' }}">
                                    <input type="radio" wire:model.live="decision" value="Validé" class="sr-only">
                                    ✅ Valider le rapport
                                </label>

                                <label class="flex items-center justify-center p-3 border rounded-lg cursor-pointer transition select-none
                                    {{ $decision === 'Rejeté' ? 'bg-rose-50 border-rose-400 text-rose-800 ring-2 ring-rose-200' : 'bg-white border-gray-200 hover:bg-gray-50 text-gray-700' }}">
                                    <input type="radio" wire:model.live="decision" value="Rejeté" class="sr-only">
                                    ❌ Rejeter / Demander des corrections
                                </label>
                            </div>
                            @error('decision') <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Champ Commentaire / Motif -->
                        <div class="space-y-1">
                            <label for="comment" class="block text-sm font-medium text-gray-700">
                                Commentaire ou Motif de rejet
                                <span class="{{ $decision === 'Rejeté' ? 'text-rose-500 font-bold' : 'text-gray-400 font-normal' }}">
                                    ({{ $decision === 'Rejeté' ? 'Obligatoire' : 'Optionnel' }})
                                </span>
                            </label>
                            <textarea id="comment" wire:model="comment" rows="4"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('comment') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror"
                                placeholder="{{ $decision === 'Rejeté' ? 'Veuillez préciser de manière claire les livrables manquants ou les corrections attendues...' : 'Indiquez ici vos remarques ou félicitations...' }}"></textarea>
                            @error('comment') <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Bouton de soumission avec état de chargement -->
                        <button type="submit" wire:loading.attr="disabled" wire:target="submitValidation"
                            class="w-full inline-flex items-center justify-center cursor-pointer px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="submitValidation">Enregistrer la décision</span>
                            <span wire:loading wire:target="submitValidation" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Traitement en cours...
                            </span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
