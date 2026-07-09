<?php

namespace App\Observers;

use App\Models\MonthlyReport;

class MonthlyReportObserver
{
    /**
     * Handle the MonthlyReport "created" event.
     */
    public function created(MonthlyReport $monthlyReport): void
    {
        //
    }

    /**
     * Handle the MonthlyReport "updated" event.
     */
    public function updated(MonthlyReport $monthlyReport): void
    {
        //
    }

    /**
     * Handle the MonthlyReport "deleted" event.
     */
    public function deleted(MonthlyReport $monthlyReport): void
    {
        //
    }

    /**
     * Handle the MonthlyReport "restored" event.
     */
    public function restored(MonthlyReport $monthlyReport): void
    {
        //
    }

    /**
     * Handle the MonthlyReport "force deleted" event.
     */
    public function forceDeleted(MonthlyReport $monthlyReport): void
    {
        //
    }
}
