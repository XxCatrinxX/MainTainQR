<?php

namespace App\Jobs;

use App\Models\OrdenServicioAudit;
use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class MonitorOrderChangesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(FirebaseNotificationService $notificationService): void
    {
        try {
            // Obtener auditorías no notificadas
            $auditorias = OrdenServicioAudit::with(['ordenServicio.user', 'ordenServicio.cliente', 'ordenServicio.equipo'])
                ->where('notificado', false)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($auditorias->isEmpty()) {
                Log::debug('MonitorOrderChanges: No hay cambios pendientes de notificar');
                return;
            }

            Log::info('MonitorOrderChanges: Procesando ' . $auditorias->count() . ' cambios');

            foreach ($auditorias as $auditoria) {
                try {
                    $orden = $auditoria->ordenServicio;
                    
                    if (!$orden || !$orden->user) {
                        Log::warning('MonitorOrderChanges: Orden o técnico no encontrado', [
                            'audit_id' => $auditoria->id,
                            'orden_id' => $auditoria->orden_servicio_id,
                        ]);
                        $auditoria->marcarNotificado();
                        continue;
                    }

                    // Construir el mensaje según el tipo de cambio
                    $titulo = $this->construirTitulo($auditoria);
                    $cuerpo = $this->construirCuerpo($auditoria);

                    // Datos adicionales
                    $datos = [
                        'orden_id' => (string)$orden->id,
                        'folio' => $orden->folio,
                        'token_rastreo' => $orden->token_rastreo,
                        'campo_cambio' => $auditoria->campo,
                        'tipo_cambio' => $auditoria->tipo_cambio,
                    ];

                    // Enviar notificación al técnico responsable
                    if ($orden->user->fcm_token) {
                        $notificationService->enviar(
                            $orden->user->fcm_token,
                            $titulo,
                            $cuerpo,
                            $datos
                        );

                        Log::info('MonitorOrderChanges: Notificación enviada al técnico', [
                            'tecnico_id' => $orden->user->id,
                            'tecnico_nombre' => $orden->user->name,
                            'orden_id' => $orden->id,
                            'cambio' => $auditoria->campo,
                        ]);
                    }

                    // Si es un cambio de cliente, también notificar al cliente
                    if ($auditoria->tipo_cambio === 'cliente' && $orden->user->fcm_token) {
                        $notificationService->enviar(
                            $orden->user->fcm_token,
                            'Cliente: ' . $titulo,
                            'El cliente: ' . $cuerpo,
                            $datos
                        );
                    }

                    // Marcar como notificado
                    $auditoria->marcarNotificado();

                } catch (\Exception $e) {
                    Log::error('MonitorOrderChanges: Error procesando auditoría', [
                        'audit_id' => $auditoria->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('MonitorOrderChanges: Completado');

        } catch (\Exception $e) {
            Log::error('MonitorOrderChanges: Error general', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Construye el título de la notificación según el cambio
     */
    private function construirTitulo(OrdenServicioAudit $auditoria): string
    {
        $mensajes = [
            'estado' => 'Cambio de Estado',
            'decision_cliente' => 'Decisión del Cliente',
            'fecha_entrega_real' => 'Equipo Entregado',
            'fecha_reparacion' => 'Reparación Iniciada',
            'mano_obra' => 'Presupuesto Actualizado',
            'fecha_estimada_entrega' => 'Fecha Estimada Actualizada',
        ];

        return $mensajes[$auditoria->campo] ?? 'Cambio en Orden: ' . $auditoria->folio;
    }

    /**
     * Construye el cuerpo del mensaje de notificación
     */
    private function construirCuerpo(OrdenServicioAudit $auditoria): string
    {
        $orden = $auditoria->ordenServicio;
        
        $mensajes = [
            'estado' => "Orden {$orden->folio}: Estado cambió a {$auditoria->valor_nuevo}",
            'decision_cliente' => "Cliente {$auditoria->valor_nuevo} el presupuesto de {$orden->folio}",
            'fecha_entrega_real' => "Equipo {$orden->folio} entregado al cliente",
            'fecha_reparacion' => "Reparación iniciada en {$orden->folio}",
            'mano_obra' => "Presupuesto actualizado: \${$auditoria->valor_nuevo}",
            'fecha_estimada_entrega' => "Nueva fecha estimada: {$auditoria->valor_nuevo}",
        ];

        return $mensajes[$auditoria->campo] ?? "Cambio en {$auditoria->campo}: {$auditoria->valor_anterior} → {$auditoria->valor_nuevo}";
    }
}
