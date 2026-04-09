<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EstadoOrdenNotificacion extends Notification implements ShouldQueue
{
    use Queueable;
    protected $orden;

    public function __construct($orden)
    {
        $this->orden = $orden;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Traducción o mapeo simple para mostrar estados más amigables
        $nombresEstado = [
            'recibido' => 'Recibido',
            'diagnostico' => 'En Diagnóstico',
            'espera' => 'Presupuestado (En Espera de Aprobación)',
            'reparacion' => 'En Reparación',
            'listo' => 'Listo para Entrega',
            'entregado' => 'Entregado al Cliente',
        ];
        $estadoVisual = $nombresEstado[$this->orden->estado] ?? strtoupper($this->orden->estado);

        return (new MailMessage)
                    ->subject('Actualización de tu Orden: ' . $this->orden->folio)
                    ->greeting('Hola ' . $this->orden->equipo->cliente->nombre . ',')
                    ->line('El estado de tu equipo ' . $this->orden->equipo->marca . ' (' . $this->orden->equipo->modelo . ') ha cambiado a: **' . $estadoVisual . '**.')
                    ->action('Ver Seguimiento y Línea de Tiempo', route('seguimiento.show', $this->orden->token_rastreo))
                    ->line('Si tu orden requiere aprobación de presupuesto, podrás aceptarlo desde el enlace de seguimiento superior.')
                    ->line('Gracias por confiar en nuestro servicio técnico.');
    }
}
