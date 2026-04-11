<?php

namespace App\Http\Controllers;

use App\Models\OrdenServicio;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Log;


class TrackingController extends Controller
{
    /**
     * Muestra la vista de seguimiento al cliente
     */
    public function show($token_rastreo)
    {
        $orden = OrdenServicio::with(['equipo.cliente', 'evidencias', 'repuestos', 'pagos', 'user'])
            ->where('token_rastreo', $token_rastreo)
            ->firstOrFail();

        return view('seguimiento.show', compact('orden'));
    }

    /**
     * Acción del cliente para aceptar su presupuesto.
     */
    public function aceptarPresupuesto(Request $request, $token_rastreo)
    {
        $orden = OrdenServicio::with('user')->where('token_rastreo', $token_rastreo)->firstOrFail();
        if ($orden->decision_cliente === 'acepta') {
            return back()->with('error', 'El presupuesto ya había sido aceptado.');
        }

        DB::beginTransaction();
        try {
            $orden->decision_cliente = 'acepta';
            $orden->estado = 'reparacion';
            $orden->save();

            //Descontar inventario
            $this->procesarInventario($orden);

            //Notificar al técnico
            $this->notificarTecnico(
                $orden,
                '✅ Presupuesto Aceptado', 
                "El cliente aceptó la reparación del equipo folio: {$orden->folio}"
            );

            foreach ($orden->repuestos as $repuesto) {
                $pieza = Inventario::lockForUpdate()->find($repuesto->id);
                if ($pieza && $pieza->stock >= $repuesto->pivot->cantidad) {
                    $pieza->decrement('stock', $repuesto->pivot->cantidad);
                } else {
                    throw new \Exception('Stock insuficiente para la pieza: ' . $repuesto->nombre_pieza);
                }
            }

            DB::commit();
            return back()->with('success', '¡Presupuesto aceptado! Comenzaremos con la reparación de tu equipo.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar la aceptación: ' . $e->getMessage());
        }
    }

    /**
     * El cliente acepta el diagnóstico via el link del correo (GET, sin login).
     */
    public function aceptar($token)
    {
        $orden = OrdenServicio::with(['equipo.cliente', 'repuestos', 'user'])
            ->where('token_rastreo', $token)
            ->firstOrFail();

        if (!in_array($orden->estado, ['espera', 'diagnostico'])) {
            return $this->retornarVistaProcesada();
        }

        DB::beginTransaction();
        try {
            
        // Descontar inventario
            $this->procesarInventario($orden);

            $orden->estado = 'aceptado';
            $orden->decision_cliente = 'acepta';
            $orden->save();

            // Notificar al técnico
            $this->notificarTecnico(
                $orden, 
                '🛠️ ¡Orden lista para reparar!', 
                "El cliente aprobó el presupuesto del folio {$orden->folio}."
            );

            DB::commit();

            return view('seguimiento.respuesta', [
                'titulo'  => '¡Reparación Aprobada!',
                'mensaje' => 'Hemos recibido tu confirmación. Tu equipo entrará a reparación a la brevedad. Te notificaremos cuando esté listo.',
                'icono'   => 'check-circle',
                'color'   => 'success',
                'orden'   => $orden,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return view('seguimiento.respuesta', [
                'titulo'  => 'Error de Inventario',
                'mensaje' => 'Lo sentimos, hubo un problema con la disponibilidad de las piezas: ' . $e->getMessage(),
                'icono'   => 'exclamation-triangle',
                'color'   => 'warning',
                'orden'   => $orden,
            ]);
        }
    }

    /**
     * El cliente rechaza el diagnóstico via el link del correo (GET, sin login).
     */
    public function rechazar($token)
    {
        $orden = OrdenServicio::with('equipo.cliente')
            ->where('token_rastreo', $token)
            ->firstOrFail();

        if (!in_array($orden->estado, ['espera', 'diagnostico'])) {
            return view('seguimiento.respuesta', [
                'titulo'  => 'Ya procesado',
                'mensaje' => 'Esta orden ya fue procesada anteriormente. No se realizaron cambios.',
                'icono'   => 'info-circle',
                'color'   => 'info',
            ]);
        }

        $orden->estado           = 'rechazado';
        $orden->decision_cliente = 'rechaza';
        $orden->save();

        app(\App\Services\FirebaseNotificationService::class)->enviar(
            $orden->user?->fcm_token ?? '',
            'Orden Rechazada',
            'El cliente rechazó la reparación',
            ['orden_id' => (string)$orden->id, 'token_rastreo' => $orden->token_rastreo]
        );

        return view('seguimiento.respuesta', [
            'titulo'  => 'Reparación Rechazada',
            'mensaje' => 'Entendemos tu decisión. Por favor acércate al taller para recoger tu equipo. Si tienes preguntas, contáctanos.',
            'icono'   => 'times-circle',
            'color'   => 'danger',
            'orden'   => $orden,
        ]);
    }

    // --- MÉTODOS PRIVADOS DE APOYO ---

    /**
     * Centraliza el envío de notificaciones al técnico asignado.
     */
    private function notificarTecnico($orden, $titulo, $mensaje)
{
    // 1. Verificamos que la orden tenga un técnico asignado y que este tenga token
    $tecnico = $orden->user; 

    if ($tecnico && $tecnico->fcm_token) {
        try {
            // Usamos el mismo método que en el Paso 3
            $messaging = app('firebase.messaging');
            
            // Creamos el objeto de notificación visual (lo que hace que vibre el cel)
            $fcmNotification = \Kreait\Firebase\Messaging\Notification::create($titulo, $mensaje);

            $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $tecnico->fcm_token)
                ->withNotification($fcmNotification)
                ->withData([
                    'id_orden' => (string)$orden->id,
                    'token_rastreo' => $orden->token_rastreo,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK' // Esto es vital
                ]);

            $messaging->send($message);
            
            \Illuminate\Support\Facades\Log::info("FCM Enviado desde Tracking: " . $orden->folio);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error FCM en Tracking: " . $e->getMessage());
        }
    } else {
        \Illuminate\Support\Facades\Log::warning("No se pudo notificar: Técnico no asignado o sin token para la orden " . $orden->folio);
    }
}

    /**
     * Procesa la baja de stock en inventario.
     */
    private function procesarInventario($orden)
    {
        foreach ($orden->repuestos as $repuesto) {
            $pieza = Inventario::lockForUpdate()->find($repuesto->id);
            if ($pieza && $pieza->stock >= $repuesto->pivot->cantidad) {
                $pieza->decrement('stock', $repuesto->pivot->cantidad);
            } else {
                throw new \Exception('Stock insuficiente para la pieza: ' . ($repuesto->nombre_pieza ?? 'ID '.$repuesto->id));
            }
        }
    }

    /**
     * Retorna la vista estándar cuando una orden ya fue aceptada/rechazada.
     */
    private function retornarVistaProcesada()
    {
        return view('seguimiento.respuesta', [
            'titulo'  => 'Ya procesado',
            'mensaje' => 'Esta orden ya fue procesada anteriormente. No se realizaron cambios.',
            'icono'   => 'info-circle',
            'color'   => 'info',
        ]);
    }
}