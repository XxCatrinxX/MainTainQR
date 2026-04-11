<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdenServicio;
use App\Models\Evidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdenTecnicoController extends Controller
{
    /**
     * Retorna los datos del equipo y del cliente a partir del código QR.
     *
     * @param string $qr_token
     * @return \Illuminate\Http\JsonResponse
     */


    public function index()
{
    $ordenes = OrdenServicio::with(['equipo.cliente', 'user'])
        ->where('user_id', Auth::id())
        ->where('estado', 'espera') // 👈 SOLO LAS QUE ACEPTÓ
        ->latest()
        ->get();

    return response()->json([
        'ordenes' => $ordenes,
    ]);
}




    public function show($id)
    {
       $orden = OrdenServicio::with([
        'equipo.cliente',
        'user',
        'evidencias',
        'repuestos'
       ])
       ->where('id', $id)
       ->where('user_id', Auth::id())
       ->firstOrFail();

       // 🔥 HISTORIAL (puedes ajustarlo después)
    $historial = $orden->evidencias->map(function ($evidencia) use ($orden) {
        return [
            'id' => $evidencia->id,
            'accion' => ucfirst($evidencia->momento),
            'descripcion' => 'Registro de evidencia',
            'tecnico' => $orden->user->name ?? 'Técnico',
            'fecha' => $evidencia->created_at->format('Y-m-d H:i'),
            'evidencias' => [
                [
                    'imageUri' => asset('storage/' . $evidencia->url_foto)
                ]
            ],
            'refacciones' => []
        ];
    });

    // 🔥 REFACCIONES
    $refacciones = $orden->repuestos->map(function ($rep) {
        return [
            'id' => $rep->id,
            'nombre' => $rep->nombre,
            'cantidad' => $rep->pivot->cantidad ?? 1
        ];
    });

    return response()->json([
        'success' => true,
        'orden' => $orden,
        'historial' => $historial,
        'refacciones' => $refacciones,
    ]);
    }

    public function showByQr($qr_token)
{
    $orden = OrdenServicio::whereHas('equipo', function ($q) use ($qr_token) {
        $q->where('qr_token', $qr_token);
    })
    ->whereNotIn('estado', ['cancelado', 'entregado'])
    ->first();

    if (!$orden) {
        return response()->json([
            'success' => false,
            'message' => 'Orden no disponible'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'orden' => $orden
    ]);
}

    public function confirmarRecepcion($id)
    {
        $orden = OrdenServicio::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($orden->estado !== 'recibido') {
            return response()->json([
                'success' => false,
                'message' => 'La orden ya no está en estado Recibido.'
            ], 400);
        }

        $orden->estado = 'diagnostico';
        $orden->save();

        return response()->json([
            'success' => true,
            'message' => 'Recepción física confirmada. Estado actualizado a Diagnóstico.',
            'estado' => $orden->estado
        ]);
    }

    /**
     * Guarda el diagnóstico realizado por el técnico con mano de obra y fotos.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeDiagnostico($id, Request $request)
    {
        $request->validate([
            'solucion_propuesta' => 'required|string',
            'mano_obra' => 'required|numeric|min:0',
            'fotos' => 'nullable|array',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $orden = OrdenServicio::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $orden->solucion_propuesta = $request->solucion_propuesta;
            $orden->mano_obra = $request->mano_obra;
            
            // Si el técnico envía el diagnóstico, la orden pasa a "espera" 
            // (que es el estado donde el cliente recibe el link para aceptar/rechazar)
            $orden->estado = 'espera'; 
            $orden->save();

            // Guardar fotos de evidencia
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $foto) {
                    $path = $foto->store('evidencias', 'public');
                    Evidencia::create([
                        'orden_servicio_id' => $orden->id,
                        'url_foto' => $path,
                        'momento' => 'diagnostico',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Diagnóstico guardado. La orden ahora espera respuesta del cliente.',
                'orden' => $orden->fresh(['evidencias'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar diagnóstico: ' . $e->getMessage()
            ], 500);
        }
    }
}
