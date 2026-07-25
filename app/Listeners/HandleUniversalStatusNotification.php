<?php

namespace App\Listeners;

use App\Events\UniversalModelStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleUniversalStatusNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UniversalModelStatusChanged $event): void
    {
        //
    }
}
