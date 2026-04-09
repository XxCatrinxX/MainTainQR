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
        // El modelo OrdenServicio tiene la relación 'user', no 'mecanico'
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

        // Podría estar en un estado 'presupuestado' o como dicta la BD 'espera' (por revisión).
        // Adaptemos para asegurar la coherencia:
        if ($orden->decision_cliente === 'acepta') {
            return back()->with('error', 'El presupuesto ya había sido aceptado.');
        }

        DB::beginTransaction();
        try {
            $orden->decision_cliente = 'acepta';
            $orden->estado = 'reparacion'; // Automáticamente empieza su reparación (?)
            $orden->save();

            // Descontar inventario físico asociado a la orden (Pivot)
            foreach ($orden->repuestos as $repuesto) {
                $pieza = Inventario::lockForUpdate()->find($repuesto->id);
                if ($pieza && $pieza->stock >= $repuesto->pivot->cantidad) {
                    $pieza->decrement('stock', $repuesto->pivot->cantidad);
                } else {
                    throw new \Exception('Stock insuficiente para la pieza: ' . $repuesto->nombre_pieza);
                }
            }

            DB::commit();

            // Aquí se enviaría la Notificación Al Técnico informando que el Cliente aceptó.

            return back()->with('success', '¡Presupuesto aceptado! Comenzaremos con la reparación de tu equipo.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar la aceptación: ' . $e->getMessage());
        }
    }

    /**
     * Acción del cliente para rechazar su presupuesto.
     */
    public function rechazarPresupuesto(Request $request, $token_rastreo)
    {
        $orden = OrdenServicio::where('token_rastreo', $token_rastreo)->firstOrFail();

        if ($orden->decision_cliente === 'rechaza') {
            return back()->with('error', 'El presupuesto ya había sido rechazado.');
        }

        $orden->decision_cliente = 'rechaza';
        $orden->estado = 'rechazado';
        $orden->save();

        return back()->with('warning', 'Has rechazado el presupuesto. Por favor acércate al taller para recoger tu equipo.');
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
            // Descontar inventario
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

        return view('seguimiento.respuesta', [
            'titulo'  => 'Reparación Rechazada',
            'mensaje' => 'Entendemos tu decisión. Por favor acércate al taller para recoger tu equipo. Si tienes preguntas, contáctanos.',
            'icono'   => 'times-circle',
            'color'   => 'danger',
            'orden'   => $orden,
        ]);
    }
}
