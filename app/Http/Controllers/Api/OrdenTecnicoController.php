<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdenServicio;
use App\Models\Evidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        ->where('user_id', auth()->id())
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
       ->where('user_id', auth()->id())
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
        $orden = OrdenServicio::with(['equipo.cliente'])
            ->whereHas('equipo', function ($q) use ($qr_token) {
                $q->where('qr_token', $qr_token);
            })
            ->where('estado', 'recibido')
            ->first();

        if(!$orden) {
            return response()->json([
                'message' => "Orden no disponible o ya fue tomada"
            ], 404);
        }

        return response()->json([
            'success' => true,
            'orden' => $orden
        ]);
    }

    public function aceptarOrden($id)
{
    $orden = OrdenServicio::where('id', $id)
        ->where('estado', 'recibido')
        ->firstOrFail();

    // 🔒 evitar que otro técnico la tome
    if ($orden->user_id !== null) {
        return response()->json([
            'message' => 'Esta orden ya fue asignada'
        ], 409);
    }

    $orden->user_id = auth()->id();
    $orden->estado = 'espera';
    $orden->save();

    return response()->json([
        'message' => 'Orden aceptada'
    ]);
}

public function rechazarOrden($id)
{
    $orden = OrdenServicio::where('id', $id)
        ->where('estado', 'recibido')
        ->firstOrFail();

    $orden->estado = 'cancelada';
    $orden->save();

    return response()->json([
        'message' => 'Orden rechazada'
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
            'fotos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // máximo 5MB
        ]);

        $orden = OrdenServicio::findOrFail($id);

        $orden->solucion_propuesta = $request->input('solucion_propuesta');
        $orden->mano_obra = $request->input('mano_obra');
        
        // Cambiamos el estado, asumiendo que el flujo natural tras diagnosticar es la presupuestación
        if ($orden->estado === 'recibido') {
             $orden->estado = 'diagnostico'; // o a presupuesto dependiendo la lógica de front
        }
        
        $orden->save();

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('evidencias', 'public');

                Evidencia::create([
                    'orden_servicio_id' => $orden->id,
                    'url_foto' => $path,
                    'momento' => in_array($orden->estado, ['recepcion','diagnostico','reparacion','finalizado']) ? $orden->estado : 'diagnostico',
                ]);
            }
        }

        return response()->json([
            'message' => 'Diagnóstico guardado exitosamente.',
            'orden' => $orden->fresh(['evidencias'])
        ]);
    }
}
