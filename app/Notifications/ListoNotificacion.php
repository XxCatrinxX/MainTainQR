<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ListoNotificacion extends Notification implements ShouldQueue
{
    use Queueable;
    protected $orden;

    public function __construct($orden)
    {
        $this->orden = $orden;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $orden = $this->orden;
        $equipo = $orden->equipo;
        $cliente = $equipo->cliente;

        return (new MailMessage)
            ->subject('¡Equipo Listo! — ' . $orden->folio)
            ->view('emails.listo', compact('orden', 'equipo', 'cliente'));
    }
}
