<div>
    <div class="max-w-md mx-auto px-4 py-8">
        <!-- Message de succès -->
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-xl shadow-sm flex items-start gap-3 animate-slideDown">
                <i class="las la-check-circle text-xl text-emerald-500 mt-0.5"></i>
                <div>
                    <p class="font-medium">{{ session('message') }}</p>
                </div>
            </div>
        @endif

        <!-- Carte principale -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden transition-all duration-300 hover:shadow-3xl">
            <!-- Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 flex justify-between items-center">
                <div class="flex items-center gap-3 text-white">
                    <i class="las la-qrcode text-3xl"></i>
                    <h3 class="text-lg font-bold tracking-tight">Scanner QR Code</h3>
                </div>
                <div class="flex items-center gap-2 px-4 py-1.5 bg-white/20 backdrop-blur-sm rounded-full">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                    </span>
                    <span class="text-white text-xs font-medium">Actif</span>
                </div>
            </div>

            <!-- Zone de scan -->
            <div class="p-6 bg-gradient-to-b from-slate-50 to-white">
                <div class="relative max-w-sm mx-auto">
                    <div 
                        x-data="{
                            scanner: null,
                            init() {
                                this.scanner = new Html5QrcodeScanner(
                                    'qr-reader',
                                    {
                                        fps: 12,
                                        qrbox: { width: 280, height: 280 },
                                        aspectRatio: 1.0
                                    },
                                    false
                                );
                                this.scanner.render(
                                    (text) => {
                                        $wire.processScan(text);
                                        // Animation flash
                                        const overlay = document.querySelector('.scanner-flash');
                                        if (overlay) {
                                            overlay.classList.add('animate-flash');
                                            setTimeout(() => overlay.classList.remove('animate-flash'), 300);
                                        }
                                    },
                                    (err) => {}
                                );
                            }
                        }"
                        wire:ignore
                        class="relative"
                    >
                        <!-- Container du reader -->
                        <div id="qr-reader" class="rounded-2xl overflow-hidden bg-black shadow-inner"></div>
                        
                        <!-- Overlay avec coins -->
                        <div class="absolute inset-0 pointer-events-none">
                            <!-- Coins -->
                            <div class="absolute top-4 left-4 w-8 h-8 border-t-4 border-l-4 border-indigo-500 rounded-tl-lg"></div>
                            <div class="absolute top-4 right-4 w-8 h-8 border-t-4 border-r-4 border-indigo-500 rounded-tr-lg"></div>
                            <div class="absolute bottom-4 left-4 w-8 h-8 border-b-4 border-l-4 border-indigo-500 rounded-bl-lg"></div>
                            <div class="absolute bottom-4 right-4 w-8 h-8 border-b-4 border-r-4 border-indigo-500 rounded-br-lg"></div>
                            
                            <!-- Ligne de scan animée -->
                            <div class="absolute top-1/2 left-1/4 right-1/4 h-0.5 bg-gradient-to-r from-transparent via-indigo-500 to-transparent animate-scanLine opacity-70"></div>
                            
                            <!-- Flash effect -->
                            <div class="scanner-flash absolute inset-0 bg-indigo-500 opacity-0 rounded-2xl transition-opacity duration-100"></div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-5 text-center space-y-1">
                        <p class="text-sm font-medium text-slate-700 flex items-center justify-center gap-2">
                            <i class="las la-camera text-indigo-500 text-lg"></i>
                            Placez un QR code dans le cadre
                        </p>
                        <p class="text-xs text-slate-400 flex items-center justify-center gap-1">
                            <i class="las la-upload"></i>
                            Ou importez une image
                        </p>
                    </div>
                </div>

                <!-- Résultat du scan -->
                <div x-data="{ result: '' }" 
                     x-on:scan-result.window="result = $event.detail"
                     x-show="result"
                     x-transition:enter.duration.300ms
                     class="mt-4 p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                    <div class="flex items-start gap-3">
                        <i class="las la-check-circle text-2xl text-emerald-500"></i>
                        <div>
                            <p class="text-sm font-medium text-emerald-800">Code détecté</p>
                            <p class="text-sm text-emerald-700 font-mono mt-1 break-all" x-text="result"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer avec actions -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1">
                        <i class="las la-shield-alt text-indigo-400"></i>
                        Sécurisé
                    </span>
                    <span class="flex items-center gap-1">
                        <i class="las la-sync-alt text-indigo-400"></i>
                        Temps réel
                    </span>
                </div>
                <button x-data="{ scanning: true }" 
                        @click="scanning = !scanning"
                        class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors duration-200 flex items-center gap-2">
                    <i class="las" :class="scanning ? 'las-pause' : 'las-play'"></i>
                    <span x-text="scanning ? 'Pause' : 'Reprendre'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Styles Tailwind personnalisés -->
    <style>
        /* Animations personnalisées */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scanLine {
            0%, 100% {
                transform: translateY(-50px);
            }
            50% {
                transform: translateY(50px);
            }
        }

        @keyframes flash {
            0% { opacity: 0; }
            50% { opacity: 0.3; }
            100% { opacity: 0; }
        }

        .animate-slideDown {
            animation: slideDown 0.3s ease-out;
        }

        .animate-scanLine {
            animation: scanLine 2s ease-in-out infinite;
        }

        .animate-flash {
            animation: flash 0.3s ease-out;
        }

        /* Personnalisation du reader HTML5-QRCode */
        #qr-reader {
            position: relative;
            z-index: 1;
        }

        #qr-reader video {
            border-radius: 1rem;
            display: block;
            width: 100%;
            height: auto;
            aspect-ratio: 1/1;
            object-fit: cover;
        }

        /* Boutons du scanner */
        #qr-reader button {
            background: rgb(99 102 241) !important;
            color: white !important;
            padding: 0.5rem 1rem !important;
            border: none !important;
            border-radius: 0.5rem !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            margin: 0.25rem !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        }

        #qr-reader button:hover {
            background: rgb(79 70 229) !important;
            transform: scale(1.02) !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4) !important;
        }

        #qr-reader button:active {
            transform: scale(0.98) !important;
        }

        #qr-reader select {
            padding: 0.375rem 0.75rem !important;
            border-radius: 0.5rem !important;
            border: 1px solid rgb(226 232 240) !important;
            background: white !important;
            font-size: 0.875rem !important;
            color: rgb(30 41 59) !important;
            margin: 0.25rem !important;
            outline: none !important;
            transition: all 0.2s ease !important;
            min-width: 120px !important;
        }

        #qr-reader select:focus {
            border-color: rgb(99 102 241) !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }

        #qr-reader input[type="file"] {
            padding: 0.5rem !important;
            border: 1px dashed rgb(203 213 225) !important;
            border-radius: 0.5rem !important;
            background: rgb(248 250 252) !important;
            font-size: 0.875rem !important;
            color: rgb(71 85 105) !important;
            margin: 0.25rem !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            width: auto !important;
        }

        #qr-reader input[type="file"]:hover {
            border-color: rgb(99 102 241) !important;
            background: rgb(241 245 249) !important;
        }

        /* Zone des contrôles du scanner */
        #qr-reader > div:last-child {
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            gap: 0.5rem !important;
            margin-top: 0.75rem !important;
            padding: 0.5rem !important;
            background: rgb(248 250 252) !important;
            border-radius: 0.75rem !important;
        }

        /* Responsive */
        @media (max-width: 640px) {
            #qr-reader button {
                padding: 0.375rem 0.75rem !important;
                font-size: 0.75rem !important;
            }
            
            #qr-reader select {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.75rem !important;
                min-width: 100px !important;
            }
            
            #qr-reader input[type="file"] {
                padding: 0.375rem !important;
                font-size: 0.75rem !important;
            }
            
            #qr-reader > div:last-child {
                flex-direction: column !important;
                align-items: stretch !important;
            }
        }
    </style>

    <!-- HTML5 QRCode -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
</div>