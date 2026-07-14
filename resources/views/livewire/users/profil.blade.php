<div class="p-0">
    {{-- Alertes Flash --}}
    @if(session('success'))
        <x-ui.alert type="success" class="mb-4 mt-8">
            {{ session('success') }}
        </x-ui.alert>
        <br>
    @endif

    {{-- Formulaire Principal --}}
    <form wire:submit="save" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <div class="p-6 space-y-8">
            {{-- Section Photo de profil --}}
            <div class="flex flex-col sm:flex-row items-center gap-5 pb-6 border-b border-gray-100">
                <div class="relative group">
                    {{-- Aperçu de la photo (Nouvelle image temporaire VS Image existante VS Initiale) --}}
                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-gray-100 shadow-sm bg-gray-50 flex items-center justify-center">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif ($user->photo && Storage::disk('public')->exists($user->photo))
                            <img src="{{ Storage::url($user->photo) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-indigo-50 text-indigo-600 font-bold text-xl flex items-center justify-center uppercase">
                                {{ substr($first_name, 0, 1) }}{{ substr($name, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    {{-- Bouton d'upload superposé --}}
                    <label for="photo-upload" class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-white text-xs font-medium">
                        <i class="las la-camera text-lg"></i>
                    </label>
                    <input type="file" id="photo-upload" wire:model="photo" class="hidden" accept="image/*">
                </div>

                <div class="text-center sm:text-left space-y-1">
                    <span class="text-sm font-bold text-gray-800 block">Photo de profil</span>
                    <span class="text-xs text-gray-400 block">Format JPG, PNG. Taille maximale de 2 Mo.</span>
                    @error('photo') <span class="text-xs text-red-600 font-medium block mt-1">{{ $message }}</span> @enderror

                    {{-- Indicateur visuel pendant le téléversement de la photo --}}
                    <span wire:loading wire:target="photo" class="text-xs text-indigo-600 font-semibold items-center gap-1 mt-1">
                        <i class="las la-spinner animate-spin"></i> Téléchargement de l'image...
                    </span>
                </div>

                <a href="{{ route('users.show', ['userId' => $user->id]) }}" wire:navigate class="inline-flex items-center justify-center bg-blue-50 gap-1.5 px-3 py-2 text-sm font-medium rounded-xl border border-blue-200 text-blue-700 hover:text-blue-600 hover:bg-blue-50/50 hover:border-blue-100 transition shadow-xs">
                    <i class="las la-eye"></i>
                    <span>Fiche</span>
                </a>
            </div>

            {{-- Grille d'informations --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Numéro d'ordre --}}
                <div class="space-y-1.5">
                    <label for="num_order" class="text-xs font-bold text-gray-700 uppercase tracking-wider">N° d'Ordre</label>
                    <x-ui.forms.input id="num_order" wire:model="num_order" disabled placeholder="Ex: ORD-001" />
                    @error('num_order') <span class="text-xs text-red-600 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Intitulé du poste --}}
                <div class="space-y-1.5">
                    <label for="job_title" class="text-xs font-bold text-gray-700 uppercase tracking-wider">Poste / Fonction</label>
                    <x-ui.forms.input id="job_title" wire:model="job_title" disabled placeholder="Ex: Développeur Senior" />
                    @error('job_title') <span class="text-xs text-red-600 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Prénom --}}
                <div class="space-y-1.5">
                    <label for="first_name" class="text-xs font-bold text-gray-700 uppercase tracking-wider">Prénom <span class="text-red-500">*</span></label>
                    <x-ui.forms.input id="first_name" wire:model="first_name" />
                    @error('first_name') <span class="text-xs text-red-600 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Nom principal --}}
                <div class="space-y-1.5">
                    <label for="name" class="text-xs font-bold text-gray-700 uppercase tracking-wider">Nom <span class="text-red-500">*</span></label>
                    <x-ui.forms.input id="name" wire:model="name" />
                    @error('name') <span class="text-xs text-red-600 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Post-nom / Nom de famille optionnel --}}
                <div class="space-y-1.5">
                    <label for="last_name" class="text-xs font-bold text-gray-700 uppercase tracking-wider">Post-nom / Nom de famille</label>
                    <x-ui.forms.input id="last_name" wire:model="last_name" />
                    @error('last_name') <span class="text-xs text-red-600 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Adresse Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-bold text-gray-700 uppercase tracking-wider">Adresse Émail <span class="text-red-500">*</span></label>
                    <x-ui.forms.input type="email" id="email" wire:model="email" />
                    @error('email') <span class="text-xs text-red-600 font-medium">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Parametre notification --}}
            <!-- SECTION 3 : Options Avancées -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="las la-envelope text-xl text-blue-600"></i> Paramètres des notifications
                </h3>

                <div class="flex flex-col justify-center mb-6">
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" wire:model="notification_database" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-semibold text-gray-900">Notifications sur la plateforme (In-App)</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-14">Si activé, vous recevrez vos alertes directement dans votre centre de notifications sur l'application (cloche).</p>
                </div>

                <div class="flex flex-col justify-center">
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" wire:model="notification_email" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-semibold text-gray-900">Alertes et rapports par courrier électronique</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-14">Si activé, vous recevrez une copie de vos validations, rappels de feuilles de temps et résumés directement dans votre boîte de messagerie.</p>
                </div>
            </div>

            {{-- Section Changement de mot de passe --}}
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="las la-shield-alt text-lg text-blue-600"></i> Sécurité des accès
                    </h3>

                    <p class="text-xs text-gray-500">Laissez ces champs vides si vous ne souhaitez pas modifier votre mot de passe actuel.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                    <div class="space-y-1.5">
                        <label for="password" class="text-xs font-bold text-gray-700 uppercase tracking-wider">Nouveau mot de passe</label>
                        <x-ui.forms.input type="password" id="password" wire:model="password" placeholder="••••••••" />
                        @error('password') <span class="text-xs text-red-600 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="text-xs font-bold text-gray-700 uppercase tracking-wider">Confirmer le mot de passe</label>
                        <x-ui.forms.input type="password" id="password_confirmation" wire:model="password_confirmation" placeholder="••••••••" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Zone d'action de validation --}}
        <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <x-ui.button type="submit" size="md" class="w-full sm:w-auto !bg-indigo-600 hover:!bg-indigo-700 shadow-sm !px-6 !rounded-xl font-bold text-white flex justify-center items-center gap-2">
                {{-- Contenu visible hors chargement --}}
                <span wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                    <i class="las la-save text-lg"></i> Sauvegarder les modifications
                </span>

                {{-- Contenu visible UNIQUEMENT pendant la sauvegarde --}}
                <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                    <i class="las la-spinner animate-spin text-lg"></i> Enregistrement en cours...
                </span>
            </x-ui.button>
        </div>

    </form>
</div>
