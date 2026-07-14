<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CalendarBusinessService
{
    /**
     * Renvoie le nom du jour en français (minuscule) pour une date donnée.
     * Ex: 'lundi', 'mardi'
     */
    public function getDayNameInFrench(string|Carbon $date): string
    {
        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        return strtolower($carbonDate->locale('fr')->dayName);
    }

    /**
     * Convertit une date pour renvoyer le mois en texte (première lettre majuscule) et l'année.
     * Ex: ['mois' => 'Juillet', 'annee' => 2026]
     */
    public function getMonthAndYearInFrench(string|Carbon $date): array
    {
        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        return [
            'mois'  => ucfirst($carbonDate->locale('fr')->monthName),
            'annee' => $carbonDate->year
        ];
    }

    /**
     * Renvoie le numéro du mois (01 à 12) à partir de son nom complet en français.
     */
    public function convertFrenchMonthNameToNumber(string $monthName): string
    {
        $months = [
            'Janvier' => '01',
            'Février' => '02',
            'Mars' => '03',
            'Avril' => '04',
            'Mai' => '05',
            'Juin' => '06',
            'Juillet' => '07',
            'Août' => '08',
            'Septembre' => '09',
            'Octobre' => '10',
            'Novembre' => '11',
            'Décembre' => '12'
        ];

        return $months[ucfirst(trim($monthName))] ?? '01';
    }

    /**
     * Calcule le nombre total de jours ouvrés (hors Samedis et Dimanches) pour un mois et une année donnés.
     */
    public function getWorkingDaysCount(string|int $month, int $year): int
    {
        // Si le mois passé est textuel (ex: 'Juillet'), on le convertit en chiffre
        $monthNumber = is_numeric($month) ? sprintf('%02d', $month) : $this->convertFrenchMonthNameToNumber($month);

        $startOfMonth = Carbon::createFromDate($year, $monthNumber, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // On utilise l'assistant de filtrage de Carbon pour exclure le week-end d'un seul coup
        return (int) $startOfMonth->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, $endOfMonth) + 1; // +1 pour inclure le jour de fin inclusif
    }

    /**
     * Renvoie la liste brute de toutes les dates (Y-m-d) ouvrées d'un mois (hors week-ends).
     * @return array<string>
     */
    public function getWorkingDatesArray(string|int $month, int $year): array
    {
        $monthNumber = is_numeric($month) ? sprintf('%02d', $month) : $this->convertFrenchMonthNameToNumber($month);

        $start = Carbon::createFromDate($year, $monthNumber, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        // Création d'une période Carbon
        $period = CarbonPeriod::create($start, $end);
        $workingDates = [];

        foreach ($period as $date) {
            if (!$date->isWeekend()) {
                $workingDates[] = $date->format('Y-m-d');
            }
        }

        return $workingDates;
    }
}
