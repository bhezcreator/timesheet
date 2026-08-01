<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class UniversalStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Model $model;

    public $recipient;

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

    public function via($notifiable): array
    {
        $channels = [];

        // Vérifier si l'utilisateur a des settings vides
        if ($notifiable->hasEmptySettings()) {
            // Journaliser que les settings sont vides
            Log::info("Utilisateur {$notifiable->id} - Settings vides, notification envoyée par défaut");

            return ['mail', 'database'];
        }

        // Récupérer le score de configuration
        $score = $notifiable->score();

        // Si le score est faible, on garde tous les canaux
        if ($score['score'] <= 3) {
            Log::info("Utilisateur {$notifiable->id} - Score de configuration faible ({$score['score']}/10), notification envoyée par défaut");

            return ['mail', 'database'];
        }

        // Vérifier les notifications email
        if ($notifiable->hasEmailNotifications()) {
            $channels[] = 'mail';
        }

        // Vérifier les notifications database
        if ($notifiable->hasDatabaseNotifications()) {
            $channels[] = 'database';
        }

        // Si aucun canal n'est activé, on utilise les canaux par défaut
        if (empty($channels)) {
            Log::info("Utilisateur {$notifiable->id} - Aucun canal activé, notification envoyée par défaut");

            return ['mail', 'database'];
        }

        // Journaliser les canaux utilisés
        Log::info("Utilisateur {$notifiable->id} - Notification envoyée via: ".implode(', ', $channels));

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔔 '.$this->title)
            ->view('emails.universal-status', [
                'title' => $this->title,
                'notifiableName' => $notifiable->name,
                'messageContent' => $this->messageContent,
                'status' => $this->status,
                'comment' => $this->comment,
                'routeUrl' => $this->routeUrl,
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'model_id' => $this->model->id,
            'model_type' => get_class($this->model),
            'title' => $this->title,
            'message' => $this->messageContent,
            'status' => $this->status,
            'comment' => $this->comment,
            'route_url' => $this->routeUrl,
            'icon' => $this->icon,
        ];
    }
}
