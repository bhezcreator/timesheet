<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'key',
        'value'
    ];

    /**
     * Configuration des logs d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'value'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('setting')
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => "Paramètre '{$this->key}' créé",
                'updated' => "Paramètre '{$this->key}' modifié",
                'deleted' => "Paramètre '{$this->key}' supprimé",
                'restored' => "Paramètre '{$this->key}' restauré",
                default => "Paramètre '{$this->key}' {$eventName}",
            });
    }

    /**
     * Récupère tous les logs d'activité pour ce paramètre
     */
    public function activityLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject');
    }

    /**
     * Récupère le dernier log d'activité
     */
    public function latestActivityLog()
    {
        return $this->morphOne(\Spatie\Activitylog\Models\Activity::class, 'subject')->latest('created_at');
    }

    /**
     * Récupère la valeur d'un paramètre
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Définit la valeur d'un paramètre avec log automatique
     */
    public static function setValue(string $key, $value, string $description = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // Log personnalisé si description fournie
        if ($description) {
            activity()
                ->performedOn($setting)
                ->causedBy(Auth::user())
                ->withProperties([
                    'key' => $key,
                    'value' => $value,
                    'action' => 'set_value'
                ])
                ->log($description);
        }

        return $setting;
    }

    /**
     * Récupère un paramètre avec une valeur par défaut
     */
    public static function get(string $key, $default = null)
    {
        return self::getValue($key, $default);
    }

    /**
     * Vérifie si un paramètre existe
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Supprime un paramètre avec log
     */
    public static function remove(string $key): bool
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        // Log avant suppression
        activity()
            ->performedOn($setting)
            ->causedBy(Auth::user())
            ->withProperties([
                'key' => $key,
                'value' => $setting->value,
                'action' => 'remove'
            ])
            ->log("Paramètre '{$key}' supprimé");

        return $setting->delete();
    }

    /**
     * Récupère tous les paramètres sous forme de tableau clé-valeur
     */
    public static function allAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Récupère tous les paramètres d'un groupe (préfixe)
     */
    public static function getByPrefix(string $prefix): array
    {
        return static::where('key', 'like', $prefix . '.%')
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Met à jour plusieurs paramètres en une fois
     */
    public static function setMultiple(array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::setValue($key, $value);
        }

        // Log global pour les mises à jour en masse
        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'settings' => $settings,
                'count' => count($settings)
            ])
            ->log('Paramètres mis à jour en masse');
    }

    /**
     * Scope pour les paramètres commençant par un préfixe
     */
    public function scopeStartsWith($query, string $prefix)
    {
        return $query->where('key', 'like', $prefix . '%');
    }

    /**
     * Scope pour les paramètres contenant une valeur
     */
    public function scopeWhereValue($query, $value)
    {
        return $query->where('value', $value);
    }

    /**
     * Accès pour la valeur typée (booléen)
     */
    public function getBooleanValueAttribute(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Accès pour la valeur typée (entier)
     */
    public function getIntegerValueAttribute(): int
    {
        return (int) $this->value;
    }

    /**
     * Accès pour la valeur typée (tableau)
     */
    public function getArrayValueAttribute(): array
    {
        if (is_array($this->value)) {
            return $this->value;
        }

        $decoded = json_decode($this->value, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Mutateur pour s'assurer que les tableaux sont encodés en JSON
     */
    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}
