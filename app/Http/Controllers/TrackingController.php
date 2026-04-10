<?php

namespace App\Http\Controllers;

use App\Models\OrdenServicio;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $orden = OrdenServicio::where('token_rastreo', $token_rastreo)->firstOrFail();

        if ($orden->decision_cliente === 'acepta') {
            return back()->with('error', 'El presupuesto ya había sido aceptado.');
        }

        DB::beginTransaction();
        try {
            $orden->decision_cliente = 'acepta';
            $orden->estado = 'reparacion';
            $orden->save();

            app(\App\Services\FirebaseNotificationService::class)->enviar(
                $orden->user?->fcm_token ?? '',
                'Presupuesto Aceptado',
                'El cliente aceptó el presupuesto',
                ['orden_id' => (string)$orden->id, 'token_rastreo' => $orden->token_rastreo]
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
        $orden = OrdenServicio::with(['equipo.cliente', 'repuestos'])
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

        DB::beginTransaction();
        try {
            foreach ($orden->repuestos as $repuesto) {
                $pieza = Inventario::lockForUpdate()->find($repuesto->id);
                if ($pieza && $pieza->stock >= $repuesto->pivot->cantidad) {
                    $pieza->decrement('stock', $repuesto->pivot->cantidad);
                } else {
                    throw new \Exception('Stock insuficiente para la pieza: ' . ($repuesto->nombre_pieza ?? 'ID '.$repuesto->id));
                }
            }

            $orden->estado           = 'aceptado';
            $orden->decision_cliente = 'acepta';
            $orden->save();

            app(\App\Services\FirebaseNotificationService::class)->enviar(
                $orden->user?->fcm_token ?? '',
                'Orden Aprobada',
                'El cliente aprobó la reparación',
                ['orden_id' => (string)$orden->id, 'token_rastreo' => $orden->token_rastreo]
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
}