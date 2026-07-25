<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

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
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->recipient->id),
        ];
    }
}
