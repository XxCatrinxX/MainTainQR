<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrdenCreadaNotificacion extends Notification implements ShouldQueue
{
    use Queueable;

    protected $orden;

    /**
     * Create a new notification instance.
     */
    public function __construct($orden)
    {
        $this->orden = $orden;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('📦 Orden de servicio creada')
            ->greeting('Hola, ' . ($this->orden->equipo->cliente->nombre ?? 'cliente'))
            ->line('Tu equipo ha sido registrado correctamente en nuestro sistema.')
            ->line('🔢 Folio: ' . $this->orden->folio)
            ->line('📌 Estado: ' . $this->orden->estado)
            ->line('En breve el técnico' . $this->orden->tecnico->nombre . ' se encargará de tu equipo.')
            ->action('Ver estado de tu orden', url('/seguimiento/' . $this->orden->token_rastreo))
            ->line('Gracias por confiar en nosotros 🙌');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
