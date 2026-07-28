<?php

namespace App\Listeners;

use App\Events\UniversalModelStatusChanged;
use App\Notifications\UniversalStatusNotification;

class HandleUniversalStatusNotification
{
    public function handle(UniversalModelStatusChanged $event): void
    {
        if ($event->recipient) {
            // Méthode standard - stocke ET broadcast
            $event->recipient->notify(new UniversalStatusNotification(
                $event->model,
                $event->title,
                $event->messageContent,
                $event->status,
                $event->comment,
                $event->routeUrl,
                $event->icon
            ));
        }
    }
}
