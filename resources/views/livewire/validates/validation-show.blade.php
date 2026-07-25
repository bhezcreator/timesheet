<div class="p-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Validation rapport.
        </h2>
    </x-slot>

    <!-- Fil d'Ariane & Bouton Retour -->
    <div class="flex items-center mb-4">
        <a href="{{ route('validations.supervisor') }}"
            wire:navigate class="font-semibold text-gray-500 inline-flex items-center gap-2 text-sm hover:text-indigo-600 transition-colors">
            <i class="las la-arrow-left text-base"></i> Retour à la liste
        </a>
    </div>

    <!-- En-tête Principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $report->fullTitle }}</h1>
                <p class="text-sm px-2 py-1 text-gray-500 mt-1 bg-blue-50 border border-blue-200 inline-flex items-center gap-1 shadow-sm rounded-full">
                    Soumis par <span class="font-semibold text-gray-700">{{ $report->user->name.' '.$report->user->first_name }}</span> le {{ $report->submitted_at?->format('d/m/Y à H:i') ?? 'N/A' }}
                </p>
            </div>

            <span class="inline-flex items-center px-3 py-2 rounded-full text-xs font-bold shadow-sm
                @if($report->status === 'soumis') bg-amber-50 text-amber-700 border border-amber-200
                @elseif($report->status === 'approuvé') bg-emerald-50 text-emerald-700 border border-emerald-200
                @else bg-rose-50 text-rose-700 border border-rose-200 @endif">
                ● Status : {{ $report->status }}
            </span>
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
                <div class="p-6 space-y-6 bg-gray-50/50 rounded-xl border border-gray-100/80">
                    <!-- Objectifs fixés -->
                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <i class="las la-bullseye text-lg"></i>
                            </div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Objectifs fixés</h3>
                        </div>
                        <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap break-words px-1">{!! nl2br(e($report->objectives)) !!}</div>
                    </div>

                    <!-- Réalisations majeures -->
                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="las la-trophy text-lg"></i>
                            </div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Réalisations majeures</h3>
                        </div>
                        <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap break-words px-1">{!! nl2br(e($report->achievements)) !!}</div>
                    </div>

                    <!-- Prochaines actions prévues -->
                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i class="las la-angle-double-right text-lg"></i>
                            </div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Prochaines actions prévues</h3>
                        </div>
                        <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap break-words px-1">{!! nl2br(e($report->next_actions)) !!}</div>
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
                    <div class="overflow-x-auto border border-gray-100 rounded-xl shadow-sm bg-white">
                        <table class="min-w-full divide-y divide-gray-200/80 text-left">
                            <thead class="bg-gray-50/70">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center gap-1.5">
                                            <i class="las la-heading text-base text-gray-400"></i>
                                            <span>Titre de l'activité</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center gap-1.5">
                                            <i class="las la-align-left text-base text-gray-400"></i>
                                            <span>Description / Tâche</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center gap-1.5">
                                            <i class="las la-info-circle text-base text-gray-400"></i>
                                            <span>Statut</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($report->activities as $activity)
                                    <tr class="hover:bg-gray-50/60 transition-colors group">
                                        <!-- Colonne Titre -->
                                        <td class="px-6 py-4 align-middle">
                                            <span class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                {{ $loop->iteration.'. '.$activity->titre ?? 'Sans titre' }}
                                            </span>
                                        </td>

                                        <!-- Colonne Description -->
                                        <td class="px-6 py-4 max-w-md align-top">
                                            <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap break-words">
                                                {!! nl2br(e($activity->description)) !!}
                                            </p>
                                            @if($activity->rejection_reason)
                                                <div class="mt-2 inline-flex items-start gap-1.5 text-xs text-rose-700 bg-rose-50/80 p-2.5 rounded-lg border border-rose-100/70 shadow-inner w-full">
                                                    <i class="las la-exclamation-circle text-base shrink-0 mt-0.5"></i>
                                                    <p class="whitespace-pre-wrap break-words">
                                                        <strong class="font-bold">Motif du rejet historique :</strong> {{ $activity->rejection_reason }}
                                                    </p>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Colonne Statut -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm  align-middle">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold shadow-sm transition-all
                                                @if($activity->status === 'approuvé') bg-emerald-50 text-emerald-700 border border-emerald-200/60
                                                @elseif($activity->status === 'rejeté') bg-rose-50 text-rose-700 border border-rose-200/60
                                                @else bg-gray-50 text-gray-600 border border-gray-200/60 @endif">
                                                <span class="w-1.5 h-1.5 rounded-full
                                                    @if($activity->status === 'approuvé') bg-emerald-500
                                                    @elseif($activity->status === 'rejeté') bg-rose-500
                                                    @else bg-gray-400 @endif"></span>
                                                <span class="capitalize">{{ $activity->status }}</span>
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
                    <i class="las la-paperclip text-xl text-indigo-500"></i>
                    <span>Pièces Jointes</span>
                </h2>

                @if($report->getMedia('attachments')->isEmpty())
                    <p class="text-sm text-gray-500 italic">Aucun document joint à ce rapport.</p>
                @else
                    <ul class="divide-y divide-gray-100 border border-gray-200 rounded-lg overflow-hidden">
                        @foreach($report->getMedia('attachments') as $media)
                            <li class="flex items-center justify-between p-3 hover:bg-gray-50 transition">
                                <div class="flex items-center space-x-3 truncate">
                                    <!-- Icône dynamique Line Awesome selon le type de fichier -->
                                    @if(str_contains($media->mime_type, 'pdf'))
                                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-rose-50 text-rose-600">
                                            <i class="las la-file-pdf text-2xl"></i>
                                        </div>
                                    @elseif(str_contains($media->mime_type, 'image'))
                                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-50 text-blue-600">
                                            <i class="las la-file-image text-2xl"></i>
                                        </div>
                                    @elseif(str_contains($media->mime_type, 'spreadsheet') || str_contains($media->mime_type, 'excel') || in_array($media->extension, ['xls', 'xlsx']))
                                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600">
                                            <i class="las la-file-excel text-2xl"></i>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gray-50 text-gray-600">
                                            <i class="las la-file-word text-2xl"></i>
                                        </div>
                                    @endif

                                    <div class="truncate">
                                        <p class="text-sm font-medium text-gray-800 truncate" title="{{ $media->file_name }}">
                                            {{ $media->file_name }}
                                        </p>
                                        <p class="text-xs text-gray-400">{{ $media->human_readable_size }}</p>
                                    </div>
                                </div>

                                <!-- Bouton intelligent : Télécharge si Excel/Word, Ouvre si PDF/Image -->
                                @if(str_contains($media->mime_type, 'spreadsheet') || str_contains($media->mime_type, 'excel') || str_contains($media->mime_type, 'word') || !str_contains($media->mime_type, 'pdf') && !str_contains($media->mime_type, 'image'))
                                    <a href="{{ $media->getUrl() }}" download class="ml-4 inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="las la-download"></i>
                                        <span>Télécharger</span>
                                    </a>
                                @else
                                    <a href="{{ $media->getUrl() }}" target="_blank" class="ml-4 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="las la-eye"></i>
                                        <span>Ouvrir</span>
                                    </a>
                                @endif
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
                        <div class="text-xs text-gray-500 mt-4 pt-3 border-t border-gray-100 flex flex-col gap-1.5">
                            <div class="flex items-center gap-1.5">
                                <i class="las la-user-check text-base text-gray-400"></i>
                                <span>Traité par : <span class="font-semibold text-gray-700">{{ $report->validation->validator->name }}</span></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="las la-calendar-check text-base text-gray-400"></i>
                                <span>Le : <span class="font-medium text-gray-700">{{ $report->validation->validated_at->format('d/m/Y à H:i') }}</span></span>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Formulaire Livewire Interactif -->
                    <form wire:submit.prevent="submitValidation" class="space-y-4">

                        <!-- Choix de l'action -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Votre Décision</label>

                            <div class="grid grid-cols-1 gap-3">
                                <!-- Option Valider -->
                                <label class="flex items-center justify-start p-4 border rounded-xl cursor-pointer transition-all select-none gap-3 shadow-sm
                                    {{ $decision === 'Validé' ? 'bg-emerald-50 border-emerald-400 text-emerald-800 ring-4 ring-emerald-100 font-semibold' : 'bg-white border-gray-200 hover:bg-gray-50 text-gray-700' }}">
                                    <input type="radio" wire:model.live="decision" value="Validé" class="sr-only">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center transition-colors
                                        {{ $decision === 'Validé' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-400' }}">
                                        <i class="las la-check-circle text-xl"></i>
                                    </div>
                                    <span>Valider le rapport</span>
                                </label>

                                <!-- Option Rejeter -->
                                <label class="flex items-center justify-start p-4 border rounded-xl cursor-pointer transition-all select-none gap-3 shadow-sm
                                    {{ $decision === 'Rejeté' ? 'bg-rose-50 border-rose-400 text-rose-800 ring-4 ring-rose-100 font-semibold' : 'bg-white border-gray-200 hover:bg-gray-50 text-gray-700' }}">
                                    <input type="radio" wire:model.live="decision" value="Rejeté" class="sr-only">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center transition-colors
                                        {{ $decision === 'Rejeté' ? 'bg-rose-500 text-white' : 'bg-gray-100 text-gray-400' }}">
                                        <i class="las la-times-circle text-xl"></i>
                                    </div>
                                    <span>Rejeter / Demander des corrections</span>
                                </label>
                            </div>

                            @error('decision') <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Champ Commentaire / Motif -->
                        <div class="space-y-2 bg-gray-50/50 p-4 rounded-xl border border-gray-100 shadow-inner">
                            <div class="flex justify-between items-center mb-4">
                                <label for="comment" class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                                    <i class="las la-comment-alt text-lg {{ $decision === 'Rejeté' ? 'text-rose-500' : 'text-indigo-500' }}"></i>
                                    Commentaire ou Motif de rejet
                                </label>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium transition-colors duration-200
                                    {{ $decision === 'Rejeté' ? 'bg-rose-100 text-rose-700 font-bold' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $decision === 'Rejeté' ? 'Obligatoire' : 'Optionnel' }}
                                </span>
                            </div>

                            <div class="relative">
                                <textarea id="comment"
                                    wire:model="comment"
                                    x-data="{
                                        resize() {
                                            $el.style.height = 'auto';
                                            $el.style.height = $el.scrollHeight + 'px'
                                        }
                                    }"
                                    x-init="resize()"
                                    @input="resize()"
                                    rows="3"
                                    class="block w-full rounded-xl border-gray-200 bg-white mt-4 px-4 py-3 text-sm text-blue-900 shadow-sm transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 resize-none overflow-hidden
                                    @error('comment') border-rose-300 focus:border-rose-500 focus:ring-rose-100 @enderror"
                                    placeholder="{{ $decision === 'Rejeté' ? 'Expliquez ici clairement les corrections attendues...' : 'Saisissez vos remarques ou encouragements...' }}"></textarea>
                            </div>

                            @error('comment')
                                <span class="text-xs text-rose-600 flex items-center gap-1 font-medium mt-1">
                                    <i class="las la-exclamation-circle text-sm"></i>
                                    {{ $message }}
                                </span>
                            @enderror
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
