<?php

namespace App\Services;

use App\Models\BlockedDay;
use App\Models\Setting;
use Carbon\Carbon;

class TimesheetLockService
{
    /**
     * Vérifie si le jour actuel est verrouillé (soit par blocage, soit par clôture mensuelle).
     */
    public function isCurrentDayLocked(): bool
    {
        return $this->isDateLocked(Carbon::today());
    }

    /**
     * Vérifie si une date spécifique est verrouillée pour la saisie des feuilles de temps.
     *
     * @param string|Carbon $date
     * @return bool
     */
    public function isDateLocked(string|Carbon $date): bool
    {
        $carbonDate = $date instanceof Carbon ? $date->startOfDay() : Carbon::parse($date)->startOfDay();

        // 1. Vérification A : Est-ce qu'il s'agit d'un jour bloqué manuellement (Férié, Maintenance, etc.) ?
        if (BlockedDay::isBlocked($carbonDate)) {
            return true;
        }

        // 2. Vérification B : Règle de clôture mensuelle (ex: Verrouiller le mois précédent après le 5 du mois en cours)
        if ($this->isMonthPeriodClosed($carbonDate)) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si la période mensuelle d'une date passée est définitivement close.
     */
    private function isMonthPeriodClosed(Carbon $date): bool
    {
        $today = Carbon::today();

        // Si la date testée appartient au mois en cours ou au futur, elle n'est pas fermée par le mensuel
        if ($date->greaterThanOrEqualTo($today->copy()->startOfMonth())) {
            return false;
        }

        // Si la date appartient à un mois antérieur au mois précédent (M-2, M-3...), elle est d'office close
        if ($date->lessThan($today->copy()->subMonth()->startOfMonth())) {
            return true;
        }

        // Si la date appartient exactement au mois précédent (M-1) :
        // On récupère le jour limite défini dans vos configurations générales (par défaut : 5)
        $lockDaySetting = Setting::where('key', 'timesheet_lock_day_of_month')->value('value');
        $lockDay = $lockDaySetting ? (int)$lockDaySetting : 5;

        // Si nous avons dépassé ce jour critique du mois actuel, le mois précédent est verrouillé
        if ($today->day > $lockDay) {
            return true;
        }

        return false;
    }
}
