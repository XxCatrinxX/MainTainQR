<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdenServicio;
use App\Models\Evidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrdenTecnicoController extends Controller
{
    public function index()
    {
        $ordenes = OrdenServicio::with(['equipo.cliente', 'user'])
            ->where('user_id', auth()->id())
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

    public function aceptarOrden($id)
    {
        $orden = OrdenServicio::where('id', $id)
            ->whereIn('estado', ['recibido'])
            ->firstOrFail();

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

        $orden->estado = 'cancelado';
        $orden->save();

        return response()->json([
            'message' => 'Orden rechazada'
        ]);
    }

    public function storeDiagnostico($id, Request $request)
    {
        $request->validate([
            'solucion_propuesta' => 'required|string',
            'mano_obra' => 'required|numeric|min:0',
            'fotos' => 'nullable|array',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $orden = OrdenServicio::findOrFail($id);

        $orden->solucion_propuesta = $request->input('solucion_propuesta');
        $orden->mano_obra = $request->input('mano_obra');

        if ($orden->estado === 'recibido') {
            $orden->estado = 'diagnostico';
        }

        $orden->save();

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('evidencias', 'public');

                Evidencia::create([
                    'orden_servicio_id' => $orden->id,
                    'url_foto' => $path,
                    'momento' => in_array($orden->estado, ['recepcion', 'diagnostico', 'reparacion', 'finalizado'])
                        ? $orden->estado
                        : 'diagnostico',
                ]);
            }
        }

        return response()->json([
            'message' => 'Diagnóstico guardado exitosamente.',
            'orden' => $orden->fresh(['evidencias'])
        ]);
    }
}