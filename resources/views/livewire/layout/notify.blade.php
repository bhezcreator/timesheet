<div class="flex items-center gap-4">
    <!-- Notifications (Dropdown Interactif Alpine.js) -->
    <div class="relative" x-data="{
        open: false,
        play() {
            $refs.audioPlayer.currentTime = 0;
            $refs.audioPlayer.play().catch(e => console.log('Audio en attente d interaction utilisateur'));
        }
        }" @play-notification-sound.window="play()">
        <!-- Fichier stocké localement dans votre projet -->
        <audio x-ref="audioPlayer" src="{{ asset('audio/notification.mp3') }}" preload="auto"></audio>

        <!-- Bouton Cloche -->
        <button @click="open = !open" class="relative cursor-pointer text-gray-500 hover:text-indigo-600 focus:outline-none transition-colors">
            <i class="las la-bell text-2xl"></i>
            @if($user->unreadNotifications->count() > 0)
                <span class="absolute -top-1 -right-1 min-w-4 h-4 px-1 bg-red-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold shadow-sm animate-pulse">
                    {{ $user->unreadNotifications->count() }}
                </span>
            @endif
        </button>

        <!-- Liste déroulante des notifications -->
        <div x-show="open"
            @click.away="open = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute right-0 mt-3 w-80 bg-white border border-gray-100 rounded-xl shadow-xl z-50 overflow-hidden"
            x-cloak>

            <div class="p-3 border-b border-gray-100 font-semibold text-xs text-gray-700 bg-gray-50/80 flex justify-between items-center">
                <span class="flex items-center gap-1">
                    <i class="las la-bell text-sm text-gray-400"></i> Centre de notifications
                </span>
                @if($user->unreadNotifications->count() > 0)
                    <button wire:click="markAllAsRead" class="text-[11px] text-indigo-600 cursor-pointer hover:text-indigo-800 hover:underline font-bold transition">
                        Tout marquer lu
                    </button>
                @endif
            </div>

            <div class="max-h-64 overflow-y-auto divide-y divide-gray-50">
                @forelse($user->notifications->take(5) as $notification)
                    <!-- Remplacement de <a> par un bouton d'action Livewire pour intercepter le clic -->
                    <button wire:click.prevent="readSingle('{{ $notification->id }}')"
                        class="p-3.5 flex items-start cursor-pointer gap-3 hover:bg-blue-100 transition-colors w-full text-left block {{ $notification->read_at ? 'opacity-60' : 'bg-indigo-50/10' }}">

                        <div class="p-2 rounded-lg bg-gray-50 border border-gray-100 shadow-sm shrink-0 flex items-center justify-center">
                            <i class="{{ $notification->data['icon'] ?? 'las la-info-circle text-gray-500' }} text-lg"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-gray-900 truncate">{{ $notification->data['title'] }}</p>
                            <p class="text-xs text-gray-600 mt-0.5 line-clamp-2 leading-relaxed">{{ $notification->data['message'] }}</p>

                            @if(!$notification->read_at && !empty($notification->data['comment']))
                                <p class="text-[11px] text-gray-500 italic bg-gray-50 p-1.5 rounded mt-1 border border-gray-100 truncate">
                                    "{{ $notification->data['comment'] }}"
                                </p>
                            @endif

                            <span class="text-[10px] text-gray-400 font-medium block mt-1.5 flex items-center gap-1">
                                <i class="las la-clock"></i> {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </button>
                @empty
                    <div class="p-6 text-center text-xs text-gray-400 italic flex flex-col items-center gap-1">
                        <i class="las la-inbox text-2xl text-gray-300"></i>
                        <span>Aucune notification pour le moment.</span>
                    </div>
                @endforelse
            </div>

            <div class="p-3 border-b border-gray-100 font-semibold text-xs text-gray-700 bg-gray-50/80 flex justify-between items-center">
                <a href="{{ route('notifications.index') }}" class="text-xs text-center text-gray-500 dark:text-gray-400 w-full hover:text-gray-700 dark:hover:text-gray-300">
                    <i class="las la-eye text-sm text-gray-400"></i> Voir tout
                </a>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center shadow-inner">
            <i class="las la-user text-indigo-600 text-xl"></i>
        </div>

        <div class="hidden md:block">
            <p class="text-sm font-semibold text-gray-800">
                {{ $user->name ?? 'Utilisateur' }}
            </p>
            <p class="text-xs text-gray-500 font-medium capitalize">
                {{ $user?->getRoleNames()->first() ?? 'Aucun rôle' }}
            </p>
        </div>
    </div>
</div>
