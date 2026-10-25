<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNotice extends Notification
{
    use Queueable;

    public function __construct(public string $title, public string $body, public string $url) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'body' => $this->body, 'url' => $this->url];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($this->body)
            ->action('Ouvrir le ticket', $this->url)
            ->line('Merci d’utiliser Ticketly.')
            ->salutation('Cordialement, l’équipe Ticketly.');
    }
}
