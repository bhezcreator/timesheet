<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class UniversalModelStatusChanged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public Model $model;

    public $recipient;

    public string $title;

    public string $messageContent;

    public string $status;

    public ?string $comment;

    public string $routeUrl;

    public string $icon;

    // Ajout d'un ID pour la notification
    public string $notificationId;

    public function __construct(
        Model $model,
        $recipient,
        string $title,
        string $messageContent,
        string $status,
        ?string $comment = null,
        string $routeUrl = '#',
        string $icon = 'las la-info-circle text-indigo-500'
    ) {
        $this->model = $model;
        $this->recipient = $recipient;
        $this->title = $title;
        $this->messageContent = $messageContent;
        $this->status = $status;
        $this->comment = $comment;
        $this->routeUrl = $routeUrl;
        $this->icon = $icon;
        $this->notificationId = (string) Str::uuid();

        // Sauvegarder directement en base de données
        // $this->storeNotification();
    }

    // protected function storeNotification(): void
    // {
    //     if ($this->recipient) {
    //         $this->recipient->notifications()->create([
    //             'id' => $this->notificationId,
    //             'type' => \App\Notifications\UniversalStatusNotification::class,
    //             'data' => [
    //                 'model_id'   => $this->model->id,
    //                 'model_type' => get_class($this->model),
    //                 'title'      => $this->title,
    //                 'message'    => $this->messageContent,
    //                 'status'     => $this->status,
    //                 'comment'    => $this->comment,
    //                 'route_url'  => $this->routeUrl,
    //                 'icon'       => $this->icon,
    //             ],
    //         ]);
    //     }
    // }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->recipient->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'UniversalModelStatusChanged';
    }

    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->notificationId,
            'title' => $this->title,
            'message' => $this->messageContent,
            'status' => $this->status,
            'icon' => $this->icon,
            'route_url' => $this->routeUrl,
        ];
    }
}
