<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DiagnosticoNotificacion extends Notification implements ShouldQueue
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
        $orden = $this->orden->loadMissing(['equipo.cliente', 'repuestos', 'evidencias']);
        $cliente = $orden->equipo->cliente;
        $equipo = $orden->equipo;

        $esReparable = (bool) $orden->es_reparable;

        if ($esReparable) {
            $manoObra = number_format($orden->mano_obra ?? 0, 2);
            $totalPiezas = number_format(
                $orden->repuestos->sum(fn ($r) => $r->pivot->cantidad * $r->pivot->precio_fijado),
                2
            );
            $total = number_format(
                ($orden->mano_obra ?? 0) +
                $orden->repuestos->sum(fn ($r) => $r->pivot->cantidad * $r->pivot->precio_fijado),
                2
            );

            $subject = 'Diagnóstico listo: ' . $orden->folio . ' — ' . $equipo->marca . ' ' . $equipo->modelo;
        } else {
            $manoObra = number_format(0, 2);
            $totalPiezas = number_format(0, 2);
            $total = number_format($orden->monto_compra_piezas ?? 0, 2);

            $subject = 'Propuesta por equipo no reparable: ' . $orden->folio . ' — ' . $equipo->marca . ' ' . $equipo->modelo;
        }

        $urlAceptar = route('orden.aceptar', $orden->token_rastreo);
        $urlRechazar = route('orden.rechazar', $orden->token_rastreo);

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.diagnostico', compact(
                'orden',
                'cliente',
                'equipo',
                'esReparable',
                'manoObra',
                'totalPiezas',
                'total',
                'urlAceptar',
                'urlRechazar'
            ));
    }
}