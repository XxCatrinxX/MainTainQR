<?php

namespace App\Observers;

use App\Models\OrdenServicio;
use App\Models\OrdenServicioAudit;
use Illuminate\Support\Facades\Auth;

class OrdenServicioObserver
{
    /**
     * Se ejecuta DESPUÉS de guardar cambios (updated event)
     */
    public function updated(OrdenServicio $orden)
    {
        // Campos que queremos rastrear
        $camposTrackeados = [
            'estado',
            'decision_cliente',
            'fecha_reparacion',
            'fecha_entrega_real',
            'mano_obra',
            'fecha_estimada_entrega',
        ];

        // Obtener los cambios
        $cambios = $orden->getChanges();

        foreach ($camposTrackeados as $campo) {
            if (array_key_exists($campo, $cambios)) {
                $valorNuevo = $cambios[$campo];
                $valorAnterior = $orden->getOriginal($campo);

                // Determinar el tipo de cambio
                $tipocambio = 'sistema';
                if (Auth::check()) {
                    $tipochangio = Auth::user()->hasRole('cliente') ? 'cliente' : 'manual';
                }

                // Registrar la auditoría
                OrdenServicioAudit::create([
                    'orden_servicio_id' => $orden->id,
                    'campo' => $campo,
                    'valor_anterior' => $valorAnterior,
                    'valor_nuevo' => $valorNuevo,
                    'tipo_cambio' => $tipochangio,
                    'usuario_responsable' => Auth::user()?->name ?? 'Sistema',
                    'notificado' => false,
                ]);
            }
        }
    }
}
