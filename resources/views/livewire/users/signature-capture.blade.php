<div>
    <!-- Messages flash de succès -->
    @if(session('success'))
        <x-ui.alert type="success" class="mb-4 mt-8">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <!-- Liste récapitulative des alertes de saisie -->
    @if($errors->any())
        <x-ui.alert type="error" class="mb-4 mt-8">
            <div class="flex flex-col gap-1">
                <span class="font-bold text-sm mb-1">Message :</span>
                <ul class="list-disc list-inside text-xs space-y-0.5 opacity-90">
                    @foreach ($errors->all() as $error)
                        @if($error !== $errors->first('permission'))
                            <li>{{ $error }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </x-ui.alert>
        <br>
    @endif

    <div class="mb-3">
        <h3 class="text-sm mb-2 font-bold text-gray-900">Signature électronique</h3>
        <p class="text-xs text-gray-500">Gérez votre émargement numérique pour la validation des fiches de temps.</p>
    </div>

    {{-- Zone d'affichage : Aperçu de la signature existante ou icône vide --}}
    <div class="flex flex-col sm:flex-row items-center gap-4 p-4 bg-gray-50 border border-gray-100 rounded-2xl">
        <div class="w-32 h-20 bg-white border border-gray-200/60 rounded-xl flex items-center justify-center p-2 shadow-2xs overflow-hidden shrink-0">
            @if($user->signature && Storage::disk('public')->exists($user->signature))
                <img src="{{ Storage::url($user->signature) }}" alt="Signature actuelle" class="max-h-full max-w-full object-contain">
            @else
                <div class="text-center text-gray-400 space-y-0.5">
                    <i class="las la-signature text-2xl block text-gray-300"></i>
                    <span class="text-[10px] font-medium italic">Aucun tracé</span>
                </div>
            @endif
        </div>

        <div class="text-center sm:text-left space-y-1.5 flex-1">
            {{-- Bouton pour afficher/masquer la zone de dessin --}}
            <button
                type="button"
                wire:click="toggleSignaturePad"
                class="inline-flex items-center gap-1.5 px-3 py-6 text-xs font-bold rounded-lg border transition-all
                cursor-pointer shadow-2xs
                {{ $showSignaturePad
                    ? 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100'
                    : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'
                }}"
            >
                <i class="las {{ $showSignaturePad ? 'la-times-circle' : 'la-pen-nib' }} text-sm"></i>
                <span>{{ $showSignaturePad ? 'Annuler le tracé' : 'Importer une signature en local' }}</span>
            </button>
        </div>

        @if($showSignaturePad)
            <div class="text-center sm:text-left space-y-2 flex-1">
                <span class="text-xs font-semibold text-gray-700 block">Importer une signature existante</span>

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    {{-- Bouton déclencheur personnalisé --}}
                    <label for="signature-file" class="inline-flex items-center gap-1.5 px-3 py-4 text-xs font-bold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs">
                        <i class="las la-file-upload text-sm"></i>
                        <span>Choisir un fichier image...</span>
                    </label>

                    {{-- CORRECTION : Suppression de wire:change ici --}}
                    <input
                        type="file"
                        id="signature-file"
                        wire:model="signatureFile"
                        class="hidden"
                        accept="image/png, image/jpeg, image/jpg"
                    >

                    {{-- Indicateur visuel pendant le téléversement de l'image --}}
                    <span wire:loading wire:target="signatureFile" class="text-xs text-indigo-600 font-semibold flex items-center gap-1">
                        <i class="las la-spinner animate-spin"></i> Traitement du fichier...
                    </span>
                </div>
            </div>
        @endif
    </div>

    {{-- Zone de dessin (Canvas) enveloppée pour gérer le cycle de vie de Livewire --}}
    <div wire:ignore class="relative my-4 max-w-xs mx-auto aspect-square bg-blue-200 border border-dashed border-blue-500 rounded-xl overflow-hidden shadow-inner">
        <canvas
            id="signature-pad"
            class="w-full h-full cursor-crosshair touch-none bg-white"
        ></canvas>
    </div>

    {{-- Boutons de contrôle --}}
    <div class="flex items-center justify-center gap-3">
        <button
            type="button"
            id="clear-btn"
            class="px-5 py-3 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition cursor-pointer"
        >
            <i class="las la-eraser mr-1"></i> Effacer
        </button>

        <button type="button" id="save-btn"
            class="px-5 py-3 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs transition inline-flex items-center gap-1.5 cursor-pointer"
        >
            <span wire:loading.remove wire:target="saveSignature">
                <i class="las la-save"></i> Enregistrer la signature
            </span>
            <span wire:loading wire:target="saveSignature">
                <i class="las la-spinner animate-spin"></i> Traitement...
            </span>
        </button>
    </div>

    {{-- Script natif pour capturer le dessin --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            const canvas = document.getElementById('signature-pad');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const clearBtn = document.getElementById('clear-btn');
            const saveBtn = document.getElementById('save-btn');
            let isDrawing = false;

            // Ajuster la résolution interne du Canvas à sa taille réelle d'affichage
            function resizeCanvas() {
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#1e293b'; // Couleur ardoise foncée
            }

            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            // --- Logique du tracé (Souris & Tactile) ---
            function getPos(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return { x: clientX - rect.left, y: clientY - rect.top };
            }

            function startDraw(e) {
                isDrawing = true;
                const pos = getPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            }

            function draw(e) {
                if (!isDrawing) return;
                e.preventDefault();
                const pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
            }

            function stopDraw() {
                isDrawing = false;
            }

            // Événements PC
            canvas.addEventListener('mousedown', startDraw);
            canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', stopDraw);

            // Événements Smartphones & Tablettes
            canvas.addEventListener('touchstart', startDraw);
            canvas.addEventListener('touchmove', draw);
            window.addEventListener('touchend', stopDraw);

            // Action : Effacer le tracé
            clearBtn.addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });

            // Action : Envoyer à Livewire
            saveBtn.addEventListener('click', () => {
                // Vérifie si le canvas est entièrement vide
                const blank = document.createElement('canvas');
                blank.width = canvas.width;
                blank.height = canvas.height;

                if (canvas.toDataURL() === blank.toDataURL()) {
                    alert("Veuillez d'abord dessiner votre signature.");
                    return;
                }

                // Convertit le dessin en chaîne DataURL (Base64 PNG)
                const dataUrl = canvas.toDataURL('image/png');

                // Envoie directement la donnée à la propriété publique PHP de Livewire, puis appelle la fonction de sauvegarde
                @this.set('signatureData', dataUrl);
                @this.call('saveSignature');
            });
        });
    </script>
</div>
