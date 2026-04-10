<?php

namespace App\Http\Controllers\Api;

use App\Models\OrdenServicio;
use App\Models\OrdenServicioAudit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderAuditController
{
    /**
     * Obtiene el historial de cambios de una orden
     * 
     * GET /api/orders/{token_rastreo}/audits
     */
    public function index($token_rastreo): JsonResponse
    {
        $orden = OrdenServicio::where('token_rastreo', $token_rastreo)
                              ->firstOrFail();

        $auditorias = OrdenServicioAudit::where('orden_servicio_id', $orden->id)
                                        ->orderBy('created_at', 'desc')
                                        ->get()
                                        ->map(function ($audit) {
                                            return [
                                                'id' => $audit->id,
                                                'campo' => $audit->campo,
                                                'valor_anterior' => $audit->valor_anterior,
                                                'valor_nuevo' => $audit->valor_nuevo,
                                                'tipo_cambio' => $audit->tipo_cambio,
                                                'usuario_responsable' => $audit->usuario_responsable,
                                                'fecha' => $audit->created_at->toIso8601String(),
                                                'hace' => $audit->created_at->diffForHumans(),
                                            ];
                                        });

        return response()->json([
            'orden_id' => $orden->id,
            'folio' => $orden->folio,
            'estado_actual' => $orden->estado,
            'cambios_totales' => $auditorias->count(),
            'auditorias' => $auditorias,
        ]);
    }

    /**
     * Obtiene solo los cambios más recientes (últimos 5 minutos)
     * Útil para polling en el frontend
     * 
     * GET /api/orders/{token_rastreo}/audits/recent
     */
    public function recent($token_rastreo): JsonResponse
    {
        $orden = OrdenServicio::where('token_rastreo', $token_rastreo)
                              ->firstOrFail();

        $auditorias = OrdenServicioAudit::where('orden_servicio_id', $orden->id)
                                        ->where('created_at', '>', now()->subMinutes(5))
                                        ->orderBy('created_at', 'desc')
                                        ->get()
                                        ->map(function ($audit) {
                                            return [
                                                'id' => $audit->id,
                                                'campo' => $audit->campo,
                                                'valor_anterior' => $audit->valor_anterior,
                                                'valor_nuevo' => $audit->valor_nuevo,
                                                'tipo_cambio' => $audit->tipo_cambio,
                                                'usuario_responsable' => $audit->usuario_responsable,
                                                'fecha' => $audit->created_at->toIso8601String(),
                                                'hace' => $audit->created_at->diffForHumans(),
                                            ];
                                        });

        return response()->json([
            'orden_id' => $orden->id,
            'folio' => $orden->folio,
            'estado_actual' => $orden->estado,
            'cambios_recientes' => $auditorias->count(),
            'auditorias' => $auditorias,
        ]);
    }

    /**
     * Stream de cambios en tiempo real (SSE)
     * GET /api/orders/{token_rastreo}/audits/stream
     */
    public function stream($token_rastreo)
    {
        $orden = OrdenServicio::where('token_rastreo', $token_rastreo)
                              ->firstOrFail();

        return response()->stream(function () use ($orden) {
            $ultimaVuelta = now();

            while (true) {
                $auditorias = OrdenServicioAudit::where('orden_servicio_id', $orden->id)
                                                ->where('created_at', '>', $ultimaVuelta)
                                                ->orderBy('created_at', 'asc')
                                                ->get();

                foreach ($auditorias as $audit) {
                    echo "data: " . json_encode([
                        'id' => $audit->id,
                        'campo' => $audit->campo,
                        'valor_anterior' => $audit->valor_anterior,
                        'valor_nuevo' => $audit->valor_nuevo,
                        'tipo_cambio' => $audit->tipo_cambio,
                        'usuario_responsable' => $audit->usuario_responsable,
                        'fecha' => $audit->created_at->toIso8601String(),
                    ]) . "\n\n";
                    $ultimaVuelta = $audit->created_at;
                }

                // Heartbeat cada 30 segundos
                echo ": heartbeat\n\n";
                sleep(5);
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
