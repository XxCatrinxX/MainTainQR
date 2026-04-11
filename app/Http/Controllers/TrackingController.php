<?php

namespace App\Http\Controllers;

use App\Models\OrdenServicio;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseNotificationService;

class TrackingController extends Controller
{
    // ... método show se queda igual ...

    /**
     * Acción del cliente para aceptar su presupuesto (desde la web de seguimiento).
     */
    public function aceptarPresupuesto(Request $request, $token_rastreo)
    {
        // Cargamos el usuario asignado para obtener su token
        $orden = OrdenServicio::with('user')->where('token_rastreo', $token_rastreo)->firstOrFail();

        if ($orden->decision_cliente === 'acepta') {
            return back()->with('error', 'El presupuesto ya había sido aceptado.');
        }

        DB::beginTransaction();
        try {
            $orden->decision_cliente = 'acepta';
            $orden->estado = 'reparacion';
            $orden->save();

            // Notificar al técnico
            $this->notificarTecnico(
                $orden, 
                '✅ Presupuesto Aceptado', 
                "El cliente aceptó la reparación del folio: {$orden->folio}"
            );

            // Lógica de inventario
            foreach ($orden->repuestos as $repuesto) {
                $pieza = Inventario::lockForUpdate()->find($repuesto->id);
                if ($pieza && $pieza->stock >= $repuesto->pivot->cantidad) {
                    $pieza->decrement('stock', $repuesto->pivot->cantidad);
                } else {
                    throw new \Exception('Stock insuficiente para: ' . $repuesto->nombre_pieza);
                }
            }

            DB::commit();
            return back()->with('success', '¡Presupuesto aceptado! Comenzaremos con la reparación.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * El cliente acepta vía link de correo.
     */
    public function aceptar($token)
    {
        $orden = OrdenServicio::with(['equipo.cliente', 'repuestos', 'user'])
            ->where('token_rastreo', $token)
            ->firstOrFail();

        if (!in_array($orden->estado, ['espera', 'diagnostico'])) {
            return $this->vistaRespuestaYaProcesado();
        }

        DB::beginTransaction();
        try {
            // ... (lógica de inventario igual a la anterior) ...

            $orden->estado = 'aceptado';
            $orden->decision_cliente = 'acepta';
            $orden->save();

            // Notificar al técnico
            $this->notificarTecnico(
                $orden, 
                '🛠️ ¡A trabajar!', 
                "El cliente aprobó la reparación del folio {$orden->folio}."
            );

            DB::commit();
            return view('seguimiento.respuesta', [
                'titulo' => '¡Reparación Aprobada!',
                'mensaje' => 'Tu equipo entrará a reparación a la brevedad.',
                'icono' => 'check-circle',
                'color' => 'success',
                'orden' => $orden,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // ... vista de error ...
        }
    }

    /**
     * El cliente rechaza vía link de correo.
     */
    public function rechazar($token)
    {
        $orden = OrdenServicio::with(['equipo.cliente', 'user'])
            ->where('token_rastreo', $token)
            ->firstOrFail();

        if (!in_array($orden->estado, ['espera', 'diagnostico'])) {
            return $this->vistaRespuestaYaProcesado();
        }

        $orden->estado = 'rechazado';
        $orden->decision_cliente = 'rechaza';
        $orden->save();

        // Notificar al técnico
        $this->notificarTecnico(
            $orden, 
            '❌ Reparación Rechazada', 
            "El cliente rechazó el presupuesto para el folio {$orden->folio}."
        );

        return view('seguimiento.respuesta', [
            'titulo' => 'Reparación Rechazada',
            'mensaje' => 'Por favor acércate al taller para recoger tu equipo.',
            'icono' => 'times-circle',
            'color' => 'danger',
            'orden' => $orden,
        ]);
    }

    /**
     * Función auxiliar para no repetir código de notificación
     */
    private function notificarTecnico($orden, $titulo, $mensaje)
    {
        if ($orden->user && $orden->user->fcm_token) {
            try {
                app(FirebaseNotificationService::class)->enviar(
                    $orden->user->fcm_token,
                    $titulo,
                    $mensaje,
                    [
                        'orden_id' => (string)$orden->id, 
                        'token_rastreo' => $orden->token_rastreo,
                        'tipo' => 'status_update'
                    ]
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("FCM Error en Tracking: " . $e->getMessage());
            }
        }
    }

    private function vistaRespuestaYaProcesado() {
        return view('seguimiento.respuesta', [
            'titulo' => 'Ya procesado',
            'mensaje' => 'Esta orden ya fue procesada anteriormente.',
            'icono' => 'info-circle',
            'color' => 'info',
        ]);
    }
}