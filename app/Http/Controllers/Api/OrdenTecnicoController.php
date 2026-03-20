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
    public function show($qr_token)
    {
        // Buscamos la orden de servicio activa que pertenezca a un equipo con ese QR_Token.
        $orden = OrdenServicio::with(['equipo.cliente', 'user', 'evidencias', 'repuestos'])
            ->whereHas('equipo', function ($query) use ($qr_token) {
                $query->where('qr_token', $qr_token);
            })
            ->whereNotIn('estado', ['entregado'])
            ->latest()
            ->first();

        if (!$orden) {
            return response()->json(['message' => 'No se encontró una orden activa para este equipo.'], 404);
        }

        /** @var \App\Models\OrdenServicio $orden */
        
        return response()->json([
            'orden' => $orden,
            'equipo' => $orden->equipo,
            'cliente' => $orden->equipo->cliente,
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
