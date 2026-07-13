<div class="py-0">
    <div class="w-full mx-auto mt-6">
        <!-- Alertes et Notifications -->
        @if(session('success'))
            <x-ui.alert type="success" class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
            <br>
        @endif

        @if($errors->any())
            <x-ui.alert type="error" class="mb-6">
                <div class="flex flex-col gap-1">
                    <span class="font-bold text-sm mb-1">Erreurs de validation détectées :</span>
                    <ul class="list-disc list-inside text-xs space-y-0.5 opacity-90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </x-ui.alert>
            <br>
        @endif

        <!-- Formulaire principal -->
        <form wire:submit.prevent="save" class="space-y-6 pb-12">

            <!-- SECTION 1 : Temps et Calendrier -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="las la-calendar text-xl text-blue-600"></i> Temps de travail &amp; Calendrier
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.forms.input
                        type="number" step="0.5"
                        label="Heures de travail par jour standard"
                        name="time_workday_hours"
                        wire:model="time_workday_hours"
                    />
                    <x-ui.forms.input
                        type="number" step="0.5"
                        label="Heures de travail par semaine standard"
                        name="time_workweek_hours"
                        wire:model="time_workweek_hours"
                    />

                    <!-- À insérer sous le paragraphe d'explication de overtime_enabled -->
                    @if($overtime_enabled)
                        <div>
                            <x-ui.forms.input
                                type="number" step="0.5"
                                label="Seuil de déclenchement hebdomadaire (Heures)"
                                name="overtime_threshold_weekly"
                                wire:model="overtime_threshold_weekly"
                            />
                            <p class="text-xs text-gray-400 mt-1">Nombre d'heures à partir duquel les heures saisies basculent en heures supplémentaires.</p>
                        </div>
                    @endif

                    <div>
                        <div class="flex items-center justify-between gap-1 mb-2">
                            <label class="text-sm text-gray-700">Premier jour de la semaine</label>
                            <span class="text-xs text-gray-400 italic">
                                Début du calendrier
                            </span>
                        </div>

                        <!-- Conteneur segmenté style iOS / Linear -->
                        <div class="grid grid-cols-2 gap-1 bg-gray-100 p-1 rounded-xl border border-gray-200/60 shadow-inner">
                            <!-- Option Lundi -->
                            <label class="relative flex items-center justify-center py-2 px-3 rounded-lg text-center cursor-pointer transition select-none {{ (int)$time_first_day_of_week === 1 ? 'bg-white text-blue-600 font-semibold shadow-sm' : 'text-gray-600 hover:text-gray-900 font-medium' }}">
                                <input type="radio" name="time_first_day_of_week" value="1" wire:model.live="time_first_day_of_week" class="sr-only">
                                <span class="text-sm tracking-tight">Lundi</span>
                            </label>

                            <!-- Option Dimanche -->
                            <label class="relative flex items-center justify-center py-2 px-3 rounded-lg text-center cursor-pointer transition select-none {{ (int)$time_first_day_of_week === 0 ? 'bg-white text-blue-600 font-semibold shadow-sm' : 'text-gray-600 hover:text-gray-900 font-medium' }}">
                                <input type="radio" name="time_first_day_of_week" value="0" wire:model.live="time_first_day_of_week" class="sr-only">
                                <span class="text-sm tracking-tight">Dimanche</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col justify-center pt-4">
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" wire:model="time_allow_weekend_logging" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Autoriser la saisie le week-end</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- SECTION 2 : Règles de Saisie / Feuilles de temps -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="las la-user-clock text-xl text-blue-600"></i> Contrôle des feuilles de temps
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.forms.input
                        type="number" step="0.5"
                        label="Saisie maximale autorisée par jour (Heures)"
                        name="timesheet_max_hours_per_day"
                        wire:model="timesheet_max_hours_per_day"
                    />

                    <x-ui.forms.input
                        type="number"
                        label="Jour limite de verrouillage du mois (Mois+1)"
                        name="timesheet_lock_day_of_month"
                        wire:model="timesheet_lock_day_of_month"
                        placeholder="Ex: 5 pour verrouiller le 5 du mois suivant"
                    />

                    <!-- Fréquence de soumission -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Fréquence de soumission</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <!-- Option Hebdomadaire -->
                            <label class="relative flex flex-col p-4 rounded-xl border {{ $timesheet_period_type === 'weekly' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600' : 'border-gray-200 bg-white hover:border-gray-300' }} cursor-pointer transition shadow-sm select-none">
                                <input type="radio" name="timesheet_period_type" value="weekly" wire:model.live="timesheet_period_type" class="sr-only">
                                <span class="block text-sm font-bold text-gray-900 mb-0.5">Hebdomadaire</span>
                                <span class="text-xs text-gray-500">Soumission et validation chaque fin de semaine.</span>
                            </label>

                            <!-- Option Bi-hebdomadaire -->
                            <label class="relative flex flex-col p-4 rounded-xl border {{ $timesheet_period_type === 'bi-weekly' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600' : 'border-gray-200 bg-white hover:border-gray-300' }} cursor-pointer transition shadow-sm select-none">
                                <input type="radio" name="timesheet_period_type" value="bi-weekly" wire:model.live="timesheet_period_type" class="sr-only">
                                <span class="block text-sm font-bold text-gray-900 mb-0.5">Toutes les 2 semaines</span>
                                <span class="text-xs text-gray-500">Soumission groupée toutes les quinzaines.</span>
                            </label>

                            <!-- Option Mensuelle -->
                            <label class="relative flex flex-col p-4 rounded-xl border {{ $timesheet_period_type === 'monthly' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600' : 'border-gray-200 bg-white hover:border-gray-300' }} cursor-pointer transition shadow-sm select-none">
                                <input type="radio" name="timesheet_period_type" value="monthly" wire:model.live="timesheet_period_type" class="sr-only">
                                <span class="block text-sm font-bold text-gray-900 mb-0.5">Mensuelle</span>
                                <span class="text-xs text-gray-500">Une seule feuille de temps par mois civil.</span>
                            </label>
                        </div>
                    </div>

                    <!-- Format de saisie du temps -->
                    <div class="col-span-1 md:col-span-2 pt-2">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Format de saisie du temps</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Option Décimal -->
                            <label class="relative flex items-center p-4 rounded-xl border {{ $format_time_input === 'decimal' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600' : 'border-gray-200 bg-white hover:border-gray-300' }} cursor-pointer transition shadow-sm select-none">
                                <input type="radio" name="format_time_input" value="decimal" wire:model.live="format_time_input" class="sr-only">
                                <div class="flex-1">
                                    <span class="block text-sm font-bold text-gray-900">Format Décimal</span>
                                    <span class="text-xs text-gray-500">Les utilisateurs saisissent des nombres (ex: <code class="bg-gray-100 px-1 py-0.5 rounded font-mono text-blue-600">7.50</code> pour 7h30).</span>
                                </div>
                            </label>

                            <!-- Option Heures/Minutes -->
                            <label class="relative flex items-center p-4 rounded-xl border {{ $format_time_input === 'duration' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600' : 'border-gray-200 bg-white hover:border-gray-300' }} cursor-pointer transition shadow-sm select-none">
                                <input type="radio" name="format_time_input" value="duration" wire:model.live="format_time_input" class="sr-only">
                                <div class="flex-1">
                                    <span class="block text-sm font-bold text-gray-900">Format Heures/Minutes</span>
                                    <span class="text-xs text-gray-500">Les utilisateurs saisissent une durée horaire (ex: <code class="bg-gray-100 px-1 py-0.5 rounded font-mono text-blue-600">07:30</code>).</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col justify-center pt-2">
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" wire:model="timesheet_allow_future_logging" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Autoriser la saisie sur les dates futures</span>
                        </label>
                    </div>

                    <div class="flex flex-col justify-center pt-2">
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" wire:model="timesheet_require_description" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Description/commentaire obligatoire</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- SECTION 3 : Options Avancées -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="las la-tools text-xl text-blue-600"></i> Module Complémentaire
                </h3>
                <div class="flex flex-col justify-center">
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" wire:model="overtime_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700 font-semibold">Activer le calcul et le suivi des heures supplémentaires</span>
                    </label>
                    <p class="text-xs text-gray-400 mt-1 ml-14">Si activé, le système calculera automatiquement les écarts par rapport au volume hebdomadaire cible.</p>
                </div>
            </div>

            <!-- NOUVELLE SECTION : Rappels et Notifications -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="las la-bell text-xl text-blue-600"></i> Rappels &amp; Notifications Automatiques
                </h3>
                <div class="space-y-6">
                    <!-- Rappel de soumission par email -->
                    <div class="flex flex-col justify-center">
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" wire:model.live="reminder_submit_enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-semibold text-gray-700">Activer les rappels automatiques par email</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-1 ml-14">Relancer automatiquement les collaborateurs n'ayant pas soumis leur feuille de temps en fin de période.</p>
                    </div>

                    <!-- Programmation de la relance des managers -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                        <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-1">Heure de relance des validations des superviseurs(Ex. 08:00)</label>
                                <x-ui.forms.input
                                    type="text"
                                    name="reminder_manager_pending"
                                    wire:model="reminder_manager_pending"
                                />
                        </div>
                        <div class="flex items-center bg-gray-50 p-4 rounded-xl border border-gray-200/60 mt-6">
                            <p class="text-xs text-gray-500 leading-relaxed">
                                <i class="las la-info-circle text-blue-500 text-sm mr-1"></i>
                                Configure le jour et l'heure d'envoi du résumé des feuilles de temps en attente d'approbation destiné aux managers (Format : <code class="bg-gray-200 px-1 py-0.5 rounded font-mono text-gray-800">Jour HH:MM</code> en anglais).
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barre d'actions fixe en bas ou simple bouton aligné à droite -->
            <div class="flex justify-end gap-3">
                <x-ui.button type="submit" class="px-6">
                    <i class="las la-save mr-1"></i> Sauvegarder les configurations
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
