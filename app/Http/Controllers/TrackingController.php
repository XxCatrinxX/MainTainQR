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
        $request->validate([
            'metodo_pago_compra' => 'nullable|in:efectivo,transferencia',
            'datos_transferencia' => 'required_if:metodo_pago_compra,transferencia|nullable|string',
        ]);

        $orden = OrdenServicio::with(['equipo.cliente', 'repuestos', 'user'])
            ->where('token_rastreo', $token_rastreo)
            ->firstOrFail();

        if (!in_array($orden->estado, ['espera', 'diagnostico', 'reparacion'])) {
            return back()->with('error', 'Esta orden ya fue procesada anteriormente.');
        }

        if ($orden->decision_cliente === 'acepta' && $orden->estado === 'para_pzas') {
            return back()->with('error', 'Esta orden ya fue aceptada anteriormente.');
        }

        DB::beginTransaction();

        try {
            $esReparable = (bool) $orden->es_reparable;

            $orden->decision_cliente = 'acepta';
            $orden->fecha_aprobacion = now();

            if ($esReparable) {
                // Si existe la lógica de procesar inventario (descontar stock)
                if (method_exists($this, 'procesarInventario')) {
                    $this->procesarInventario($orden);
                }

                $orden->estado = 'reparacion';
                $orden->fecha_reparacion = now();

                $this->notificarTecnico(
                    $orden,
                    '✅ Presupuesto Aceptado',
                    "El cliente aceptó la reparación del equipo folio: {$orden->folio}"
                );

                $mensaje = '¡Presupuesto aceptado! Comenzaremos con la reparación de tu equipo.';
            } else {
                $orden->estado = 'para_pzas';
                $orden->metodo_pago_compra = $request->metodo_pago_compra;
                $orden->datos_transferencia = $request->datos_transferencia;

                $this->notificarTecnico(
                    $orden,
                    '✅ Oferta por piezas aceptada',
                    "El cliente aceptó la propuesta por piezas del equipo folio: {$orden->folio}"
                );

                $mensaje = '¡Propuesta aceptada! ' . ($request->metodo_pago_compra === 'transferencia' ? 'Procesaremos tu pago a los datos proporcionados.' : 'Podrás pasar por tu efectivo a sucursal.');
            }

            $orden->save();

            DB::commit();
            return back()->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar la aceptación: ' . $e->getMessage());
        }
    }

    /**
     * Acción del cliente para rechazar la propuesta.
     */
    public function rechazarPresupuesto(Request $request, $token_rastreo)
    {
        $orden = OrdenServicio::with(['equipo.cliente', 'user'])
            ->where('token_rastreo', $token_rastreo)
            ->firstOrFail();

        if (!in_array($orden->estado, ['espera', 'diagnostico'])) {
            return back()->with('error', 'Esta orden ya fue procesada anteriormente.');
        }

        $esReparable = (bool)$orden->es_reparable;

        $orden->estado = 'rechazado';
        $orden->decision_cliente = 'rechaza';
        $orden->fecha_aprobacion = now();
        $orden->save();

        $this->notificarTecnico(
            $orden,
            $esReparable ? '❌ Reparación rechazada' : '❌ Oferta por piezas rechazada',
            $esReparable
                ? "El cliente rechazó la reparación del folio {$orden->folio}."
                : "El cliente rechazó la oferta por piezas del folio {$orden->folio}."
        );

        return back()->with('success', 'Has rechazado la propuesta. El técnico ha sido notificado.');
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

    if ($orden->decision_cliente === 'acepta' || in_array($orden->estado, ['reparacion', 'para_pzas'])) {
        return $this->retornarVistaProcesada();
    }

    DB::beginTransaction();

    try {
        $esReparable = (bool) $orden->es_reparable;

        $orden->decision_cliente = 'acepta';
        $orden->fecha_aprobacion = now();

        if ($esReparable) {
            $this->procesarInventario($orden);

            $orden->estado = 'reparacion';
            $orden->fecha_reparacion = now();

            $this->notificarTecnico(
                $orden,
                '🛠️ Reparación autorizada',
                "El cliente aprobó la reparación del folio {$orden->folio}."
            );

            $titulo = '¡Reparación Aprobada!';
            $mensaje = 'Hemos recibido tu confirmación. Tu equipo entrará a reparación a la brevedad. Te notificaremos cuando esté listo.';
        } else {
            $orden->estado = 'para_pzas';

            $this->notificarTecnico(
                $orden,
                '🧩 Equipo aceptado para piezas',
                "El cliente aceptó la oferta por piezas del folio {$orden->folio}."
            );

            $titulo = '¡Oferta Aceptada!';
            $mensaje = 'Hemos recibido tu confirmación. Tu equipo pasará al proceso para piezas.';
        }

        $orden->save();

        DB::commit();

        return view('seguimiento.respuesta', [
            'titulo'  => $titulo,
            'mensaje' => $mensaje,
            'icono'   => 'check-circle',
            'color'   => 'success',
            'orden'   => $orden,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        return view('seguimiento.respuesta', [
            'titulo'  => 'Error al procesar la respuesta',
            'mensaje' => 'Lo sentimos, ocurrió un problema al procesar tu decisión: ' . $e->getMessage(),
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
    $orden = OrdenServicio::with(['equipo.cliente', 'user'])
        ->where('token_rastreo', $token)
        ->firstOrFail();

    if (!in_array($orden->estado, ['espera', 'diagnostico'])) {
        return view('seguimiento.respuesta', [
            'titulo'  => 'Ya procesado',
            'mensaje' => 'Esta orden ya fue procesada anteriormente. No se realizaron cambios.',
            'icono'   => 'info-circle',
            'color'   => 'info',
            'orden'   => $orden,
        ]);
    }

    $esReparable = (bool) $orden->es_reparable;

    $orden->estado = 'rechazado';
    $orden->decision_cliente = 'rechaza';
    $orden->fecha_aprobacion = now();
    $orden->save();

    $this->notificarTecnico(
        $orden,
        $esReparable ? '❌ Reparación rechazada' : '❌ Oferta por piezas rechazada',
        $esReparable
            ? "El cliente rechazó la reparación del folio {$orden->folio}."
            : "El cliente rechazó la oferta por piezas del folio {$orden->folio}."
    );

    return view('seguimiento.respuesta', [
        'titulo'  => $esReparable ? 'Reparación Rechazada' : 'Oferta Rechazada',
        'mensaje' => 'Hemos registrado tu decisión. Tu equipo quedará pendiente para que pases por él en sucursal.',
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
            throw new \Exception('Stock insuficiente para la pieza: ' . ($repuesto->nombre_pieza ?? 'ID ' . $repuesto->id));
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