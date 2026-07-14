<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class AppSettingsService
{
    /**
     * Clé unique utilisée pour stocker les configurations en cache.
     */
    private const CACHE_KEY = 'app_global_settings';

    /**
     * Durée de vie du cache en secondes (ici 24 heures).
     */
    private const CACHE_TTL = 86400;

    /**
     * Récupère la valeur d'un paramètre système avec conversion automatique du type de donnée.
     *
     * @param string $key La clé du paramètre (ex: 'overtime_enabled')
     * @param mixed $default Valeur de retour par défaut si la clé n'existe pas en BDD
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Récupération de l'intégralité des configurations via le cache pour optimiser les performances
        $settings = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        // Si la clé n'existe pas, retourner la valeur par défaut spécifiée
        if (!array_key_exists($key, $settings)) {
            return $default;
        }

        $value = $settings[$key];

        // Transtypage (Casting) dynamique de la valeur TEXT vers son type réel natif PHP
        if ($value === '1' || $value === '0') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if (is_numeric($value)) {
            return (strpos($value, '.') !== false) ? (float)$value : (int)$value;
        }

        return $value;
    }

    /**
     * Vide le cache des paramètres (À appeler impérativement lors de la modification des réglages).
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
