<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DiagnosticoNotificacion extends Notification
{

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
        $orden  = $this->orden;
        $cliente = $orden->equipo->cliente;
        $equipo  = $orden->equipo;

        $manoObra     = number_format($orden->mano_obra ?? 0, 2);
        $totalPiezas  = number_format(
            $orden->repuestos->sum(fn($r) => $r->pivot->cantidad * $r->pivot->precio_fijado), 2
        );
        $total = number_format(($orden->mano_obra ?? 0) +
            $orden->repuestos->sum(fn($r) => $r->pivot->cantidad * $r->pivot->precio_fijado), 2);

        $urlAceptar  = route('orden.aceptar', $orden->token_rastreo);
        $urlRechazar = route('orden.rechazar', $orden->token_rastreo);

        return (new MailMessage)
            ->subject('Diagnóstico listo: ' . $orden->folio . ' — ' . $equipo->marca . ' ' . $equipo->modelo)
            ->view('emails.diagnostico', compact('orden', 'cliente', 'equipo', 'manoObra', 'totalPiezas', 'total', 'urlAceptar', 'urlRechazar'));
    }
}
