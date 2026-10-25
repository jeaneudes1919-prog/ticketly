<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Vous avez demandé à réinitialiser le mot de passe de votre compte Ticketly.')
            ->action('Choisir un nouveau mot de passe', $url)
            ->line('Ce lien est valable 60 minutes. Si vous n’êtes pas à l’origine de cette demande, ignorez simplement cet email.')
            ->line('Merci d’utiliser Ticketly.')
            ->salutation('Cordialement, l’équipe Ticketly.');
    }
}
