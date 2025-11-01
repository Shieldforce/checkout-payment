<?php

namespace Shieldforce\CheckoutPayment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CheckoutStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public string $status,
        public string $message,
        public string $corporateName,
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return [
            'mail',
            'database',
            'broadcast',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line($this->message ?? 'sem mensagem')
            ->action('Ir para painel', url('/'))
            ->line($this->corporateName ?? 'Sem nome');
    }

    public function toDatabase($notifiable)
    {
        return [
            'status' => $this->status ?? 'processing',
            'message' => $this->message ?? 'Sem mensagem',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'status' => $this->status ?? 'processing',
            'message' => $this->message ?? 'Sem mensagem',
        ]);
    }
}
