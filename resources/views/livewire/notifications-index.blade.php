<div class="p-0">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Notifications
        </h2>
    </x-slot>

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-1">
                <i class="las la-bell text-blue-600"></i>
                Notifications
                @if($notificationCount > 0)
                    <span class="text-sm font-medium bg-red-100 text-red-600 px-3 py-0.5 rounded-full">
                        {{ $notificationCount }} nouvelle(s)
                    </span>
                @endif
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Gérez toutes vos notifications en un seul endroit
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if($notificationCount > 0)
                <button wire:click="markAllAsRead"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 flex items-center gap-2">
                    <i class="las la-check-double"></i>
                    Tout marquer comme lu
                </button>
            @endif

            <button wire:click="deleteAllRead"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200 flex items-center gap-2">
                <i class="las la-trash-alt"></i>
                Supprimer les lues
            </button>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div class="flex flex-wrap gap-2">
                <button wire:click="$set('filter', 'all')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200
                                {{ $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="las la-list-ul mr-1"></i>
                    Toutes
                </button>
                <button wire:click="$set('filter', 'unread')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200
                                {{ $filter === 'unread' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="las la-envelope mr-1"></i>
                    Non lues
                    @if($notificationCount > 0)
                        <span class="ml-1 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $notificationCount }}</span>
                    @endif
                </button>
                <button wire:click="$set('filter', 'read')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200
                                {{ $filter === 'read' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="las la-check-circle mr-1"></i>
                    Lues
                </button>
            </div>

            <div class="relative w-full sm:w-64">
                <i class="las la-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text"
                    wire:model.debounce.300ms="search"
                    placeholder="Rechercher une notification..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
        </div>
    </div>

    <!-- Alertes de session -->
    @if(session('success'))
        <x-ui.alert type="success" class="mb-4">
            {{ session('success') }}
        </x-ui.alert> <br>
    @endif

    <!-- Liste des notifications -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="divide-y divide-gray-100">
            @forelse($notifications as $notification)
                <div class="p-4 hover:bg-gray-100 transition-all duration-200 group {{ $notification->read_at ? 'opacity-75' : 'bg-blue-50/50' }}">
                    <div class="flex items-start gap-4">
                        <!-- Icône -->
                        <div class="shrink-0">
                            <div class="p-2.5 rounded-xl
                                        {{ $notification->read_at
                                            ? 'bg-gray-200 text-gray-500'
                                            : 'bg-blue-200 text-blue-600' }}">
                                <i class="{{ $notification->data['icon'] ?? 'las la-bell' }} text-xl"></i>
                            </div>
                        </div>

                        <!-- Contenu -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 cursor-pointer" wire:click.prevent="readSingle('{{ $notification->id }}')">
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        {{ $notification->data['title'] ?? 'Notification' }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1 leading-relaxed">
                                        {{ $notification->data['message'] ?? '' }}
                                    </p>

                                    @if(!$notification->read_at && !empty($notification->data['comment']))
                                        <div class="mt-2 p-2 bg-gray-50 rounded-lg border border-gray-200">
                                            <p class="text-xs text-gray-500 italic">
                                                <i class="las la-quote-left mr-1"></i>
                                                "{{ $notification->data['comment'] }}"
                                            </p>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-4 mt-2">
                                        <span class="text-xs text-gray-400 flex items-center gap-1">
                                            <i class="las la-clock"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                        @if(!$notification->read_at)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="las la-dot-circle text-xs mr-1"></i>
                                                Nouveau
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-1 shrink-0">
                                    @if(!$notification->read_at)
                                        <button wire:click="readSingle('{{ $notification->id }}')"
                                                class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors cursor-pointer"
                                                title="Marquer comme lu et ouvrir">
                                            <i class="las la-external-link-alt text-lg"></i>
                                        </button>
                                    @else
                                        <button wire:click="deleteNotification('{{ $notification->id }}')"
                                                class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors opacity-0 group-hover:opacity-100 cursor-pointer"
                                                title="Supprimer">
                                            <i class="las la-trash text-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="mx-auto w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="las la-inbox text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">
                        Aucune notification
                    </h3>
                    <p class="text-sm text-gray-500">
                        @if($filter === 'all')
                            Vous n'avez aucune notification pour le moment.
                        @elseif($filter === 'unread')
                            Vous n'avez aucune notification non lue.
                        @else
                            Vous n'avez aucune notification lue.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Liens de pagination réactifs -->
        <div class="mt-5">
            <x-ui.pagination :paginator="$notifications" />
        </div>
    </div>
</div>
