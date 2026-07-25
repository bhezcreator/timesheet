<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class UniversalStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Model $model;
    public string $title;
    public string $messageContent;
    public string $status;
    public ?string $comment;
    public string $routeUrl;
    public string $icon;

    /**
     * Le constructeur accepte des paramètres totalement génériques.
     */
    public function __construct(
        Model $model,
        string $title,
        string $messageContent,
        string $status,
        ?string $comment = null,
        string $routeUrl = '#',
        string $icon = 'las la-info-circle text-indigo-500'
    ) {
        $this->model = $model;
        $this->title = $title;
        $this->messageContent = $messageContent;
        $this->status = $status;
        $this->comment = $comment;
        $this->routeUrl = $routeUrl;
        $this->icon = $icon;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting("Bonjour {$notifiable->name},")
            ->line($this->messageContent)
            ->line("Statut actuel : **" . ucfirst($this->status) . "**");

        if ($this->comment) {
            $mail->line("Note / Motif :")
                ->line("> " . $this->comment);
        }

        return $mail
            ->action('Ouvrir l\'élément', $this->routeUrl)
            ->line('Merci pour votre collaboration.');
    }

    public function toArray($notifiable): array
    {
        return [
            'model_id'   => $this->model->id,
            'model_type' => get_class($this->model),
            'title'      => $this->title,
            'message'    => $this->messageContent,
            'status'     => $this->status,
            'comment'    => $this->comment,
            'route_url'  => $this->routeUrl,
            'icon'       => $this->icon,
        ];
    }
}
