<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OnlineUsersService
{
    /**
     * Durée de validité du cache en minutes
     */
    protected int $cacheDuration = 2;

    /**
     * Récupère le nombre d'utilisateurs en ligne
     */
    public function getOnlineUsersCount(): int
    {
        return Cache::remember(
            'online_users_count',
            now()->addMinutes($this->cacheDuration),
            fn () => $this->countActiveSessions()
        );
    }

    /**
     * Compte les sessions actives
     */
    private function countActiveSessions(): int
    {
        try {
            $sessionLifetime = config('session.lifetime', 120);
            $activeThreshold = now()->subMinutes($sessionLifetime)->timestamp;

            $query = DB::table('sessions')
                ->where('last_activity', '>=', $activeThreshold);

            // Ne compter que les utilisateurs authentifiés si possible
            if (Schema::hasColumn('sessions', 'user_id')) {
                $query->whereNotNull('user_id');
            }

            return $query->count();
        } catch (\Exception $e) {
            Log::error('Erreur lors du comptage des sessions actives', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Invalide le cache
     */
    public function clearCache(): void
    {
        Cache::forget('online_users_count');
    }
}
