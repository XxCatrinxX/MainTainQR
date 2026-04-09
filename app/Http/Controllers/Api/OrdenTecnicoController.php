<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleTecnico;
use App\Models\Evidencia;
use App\Models\Inventario;
use App\Models\OrdenServicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrdenTecnicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $ordenes = $this->baseOrdenQuery($request)
            ->when(
                $request->filled('estado'),
                fn ($query) => $query->where('estado', $request->input('estado'))
            )
            ->latest()
            ->get()
            ->map(fn (OrdenServicio $orden) => $this->transformOrden($orden))
            ->values();

        return response()->json([
            'success' => true,
            'ordenes' => $ordenes,
        ]);
    }

    public function showById(Request $request, int $id): JsonResponse
    {
        $orden = $this->findOrdenById($request, $id);
        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la orden solicitada.',
            ], 404);
        }

        return response()->json($this->buildDetalleResponse($orden));
    }

    public function showByQrOrSerie(Request $request, string $query): JsonResponse
    {
        $orden = $this->baseOrdenQuery($request)
            ->whereHas('equipo', function ($builder) use ($query) {
                $builder->where('qr_token', $query)
                    ->orWhere('numero_serie', $query);
            })
            ->whereNotIn('estado', ['entregado'])
            ->latest()
            ->first();

        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro una orden activa para este equipo.',
            ], 404);
        }

        return response()->json($this->buildDetalleResponse($orden));
    }

    public function show(Request $request, string $qr_token): JsonResponse
    {
        return $this->showByQrOrSerie($request, $qr_token);
    }

    public function storeDiagnostico(int $id, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'diagnostico' => 'nullable|string',
            'solucion_propuesta' => 'nullable|string',
            'mano_obra' => 'nullable|numeric|min:0',
            'fotos' => 'nullable|array',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $descripcion = trim((string) ($payload['diagnostico'] ?? $payload['solucion_propuesta'] ?? ''));
        if ($descripcion === '') {
            return response()->json([
                'success' => false,
                'message' => 'El diagnostico es obligatorio.',
            ], 422);
        }

        $orden = $this->findOrdenById($request, $id);
        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la orden solicitada.',
            ], 404);
        }

        $orden->solucion_propuesta = $descripcion;
        if (array_key_exists('mano_obra', $payload) && $payload['mano_obra'] !== null) {
            $orden->mano_obra = $payload['mano_obra'];
        }

        if ($orden->estado === 'recibido') {
            $orden->estado = 'diagnostico';
        }

        $orden->save();

        DetalleTecnico::updateOrCreate(
            ['orden_servicio_id' => $orden->id],
            ['solucion_propuesta' => $descripcion]
        );

        $this->guardarFotos($request, $orden, 'diagnostico');
        $orden->loadMissing(['equipo.cliente', 'user', 'evidencias', 'repuestos', 'detallesTecnicos']);

        return response()->json([
            'success' => true,
            'message' => 'Diagnostico guardado exitosamente.',
            'orden' => $this->transformOrden($orden),
            'historial' => $this->buildHistorial($orden),
            'refacciones' => $this->buildRefaccionesDetalle($orden),
        ]);
    }

    public function storeReparacion(int $id, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'acciones' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        $orden = $this->findOrdenById($request, $id);
        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la orden solicitada.',
            ], 404);
        }

        DetalleTecnico::updateOrCreate(
            ['orden_servicio_id' => $orden->id],
            [
                'trabajo_finalizado' => $payload['acciones'],
                'observaciones_internas' => $payload['observaciones'] ?? null,
            ]
        );

        $orden->estado = 'reparacion';
        $orden->fecha_reparacion = $orden->fecha_reparacion ?: now();
        $orden->save();

        return response()->json([
            'success' => true,
            'message' => 'Reparacion registrada correctamente.',
        ]);
    }

    public function finalizar(int $id, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'observaciones_finales' => 'nullable|string',
        ]);

        $orden = $this->findOrdenById($request, $id);
        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la orden solicitada.',
            ], 404);
        }

        if (!empty($payload['observaciones_finales'])) {
            $detalle = DetalleTecnico::firstOrNew(['orden_servicio_id' => $orden->id]);
            $detalle->observaciones_internas = $payload['observaciones_finales'];
            $detalle->save();
        }

        $orden->estado = 'listo';
        $orden->save();

        return response()->json([
            'success' => true,
            'message' => 'La orden fue marcada como lista.',
        ]);
    }

    public function refacciones(Request $request): JsonResponse
    {
        $buscar = trim((string) $request->input('buscar', ''));

        $refacciones = Inventario::query()
            ->when(
                $buscar !== '',
                fn ($query) => $query->where(function ($builder) use ($buscar) {
                    $builder->where('nombre_pieza', 'like', "%{$buscar}%")
                        ->orWhere('sku', 'like', "%{$buscar}%");
                })
            )
            ->where('stock', '>', 0)
            ->orderBy('nombre_pieza')
            ->get()
            ->map(fn (Inventario $pieza) => [
                'id' => $pieza->id,
                'nombre' => $pieza->nombre_pieza,
                'codigo' => $pieza->sku,
                'stock' => $pieza->stock,
                'precio' => (float) $pieza->precio_venta,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'refacciones' => $refacciones,
        ]);
    }

    public function usarRefaccion(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'orden_id' => 'required|integer',
            'refaccion_id' => 'required|integer|exists:inventario,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $orden = $this->findOrdenById($request, (int) $payload['orden_id']);
        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la orden solicitada.',
            ], 404);
        }

        $pieza = Inventario::findOrFail($payload['refaccion_id']);
        if ($pieza->stock < $payload['cantidad']) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente para registrar la refaccion.',
            ], 422);
        }

        DB::transaction(function () use ($orden, $pieza, $payload) {
            $existente = $orden->repuestos()
                ->where('inventario_id', $pieza->id)
                ->first();

            $cantidadActual = (int) ($existente?->pivot?->cantidad ?? 0);
            $nuevaCantidad = $cantidadActual + (int) $payload['cantidad'];

            $orden->repuestos()->syncWithoutDetaching([
                $pieza->id => [
                    'cantidad' => $nuevaCantidad,
                    'precio_fijado' => $pieza->precio_venta,
                ],
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Refaccion registrada correctamente.',
        ]);
    }

    protected function baseOrdenQuery(Request $request)
    {
        return OrdenServicio::query()
            ->with(['equipo.cliente', 'user', 'evidencias', 'repuestos', 'detallesTecnicos'])
            ->where('user_id', $request->user()->id);
    }

    protected function findOrdenById(Request $request, int $id): ?OrdenServicio
    {
        return $this->baseOrdenQuery($request)->whereKey($id)->first();
    }

    protected function buildDetalleResponse(OrdenServicio $orden): array
    {
        return [
            'success' => true,
            'orden' => $this->transformOrden($orden),
            'historial' => $this->buildHistorial($orden),
            'refacciones' => $this->buildRefaccionesDetalle($orden),
        ];
    }

    protected function transformOrden(OrdenServicio $orden): array
    {
        $detalleTecnico = $orden->detallesTecnicos;

        return [
            'id' => $orden->id,
            'folio' => $orden->folio,
            'falla_reportada' => $orden->falla_reportada,
            'solucion_propuesta' => $orden->solucion_propuesta ?: $detalleTecnico?->solucion_propuesta,
            'estado_fisico' => $orden->estado_fisico,
            'estado' => $orden->estado,
            'fecha_recepcion' => optional($orden->fecha_recepcion)->format('Y-m-d H:i'),
            'prioridad' => $this->mapPrioridad($orden->estado),
            'fecha_estimada_entrega' => optional($orden->fecha_estimada_entrega)->format('Y-m-d H:i'),
            'diagnostico' => $orden->solucion_propuesta ?: $detalleTecnico?->solucion_propuesta,
            'observaciones' => $detalleTecnico?->observaciones_internas,
            'equipo' => [
                'id' => $orden->equipo?->id,
                'tipo' => $orden->equipo?->tipo,
                'marca' => $orden->equipo?->marca,
                'modelo' => $orden->equipo?->modelo,
                'numero_serie' => $orden->equipo?->numero_serie,
                'cliente' => [
                    'id' => $orden->equipo?->cliente?->id,
                    'nombre' => $orden->equipo?->cliente?->nombre,
                    'apellido_paterno' => $orden->equipo?->cliente?->apellido_paterno,
                    'apellido_materno' => $orden->equipo?->cliente?->apellido_materno,
                    'correo' => $orden->equipo?->cliente?->correo,
                    'telefono' => $orden->equipo?->cliente?->telefono,
                ],
            ],
        ];
    }

    protected function buildHistorial(OrdenServicio $orden): array
    {
        $detalleTecnico = $orden->detallesTecnicos;
        $historial = collect();

        $diagnostico = $orden->solucion_propuesta ?: $detalleTecnico?->solucion_propuesta;
        if (!empty($diagnostico)) {
            $historial->push([
                'id' => ($orden->id * 10) + 1,
                'accion' => 'Diagnostico registrado',
                'descripcion' => $diagnostico,
                'tecnico' => $this->nombreTecnico($orden),
                'fecha' => optional($orden->updated_at)->format('Y-m-d H:i') ?? '',
                'evidencias' => $this->buildEvidencias($orden->evidencias, 'diagnostico'),
                'refacciones' => [],
            ]);
        }

        if (!empty($detalleTecnico?->trabajo_finalizado)) {
            $historial->push([
                'id' => ($orden->id * 10) + 2,
                'accion' => 'Reparacion registrada',
                'descripcion' => $detalleTecnico->trabajo_finalizado,
                'tecnico' => $this->nombreTecnico($orden),
                'fecha' => optional($detalleTecnico->updated_at)->format('Y-m-d H:i') ?? '',
                'evidencias' => $this->buildEvidencias($orden->evidencias, 'reparacion'),
                'refacciones' => $this->buildRefaccionesRegistro($orden),
            ]);
        }

        if ($historial->isEmpty() && $orden->evidencias->isNotEmpty()) {
            $historial->push([
                'id' => ($orden->id * 10) + 3,
                'accion' => 'Evidencias registradas',
                'descripcion' => 'Se encontraron evidencias asociadas a la orden.',
                'tecnico' => $this->nombreTecnico($orden),
                'fecha' => optional($orden->updated_at)->format('Y-m-d H:i') ?? '',
                'evidencias' => $this->buildEvidencias($orden->evidencias, null),
                'refacciones' => $this->buildRefaccionesRegistro($orden),
            ]);
        }

        return $historial->values()->all();
    }

    protected function buildEvidencias(Collection $evidencias, ?string $momento): array
    {
        $filtradas = $momento
            ? $evidencias->filter(fn (Evidencia $evidencia) => $evidencia->momento === $momento)
            : $evidencias;

        return $filtradas
            ->values()
            ->map(function (Evidencia $evidencia, int $index) {
                return [
                    'id' => $evidencia->id,
                    'trabajoId' => $evidencia->orden_servicio_id,
                    'nombre' => 'Evidencia ' . ($index + 1),
                    'origen' => ucfirst($evidencia->momento ?: 'registro'),
                    'fecha' => optional($evidencia->created_at)->format('Y-m-d H:i') ?? '',
                    'comentario' => null,
                    'imageUri' => url(Storage::url($evidencia->url_foto)),
                ];
            })
            ->all();
    }

    protected function buildRefaccionesDetalle(OrdenServicio $orden): array
    {
        return $orden->repuestos
            ->map(function (Inventario $pieza) {
                return [
                    'id' => $pieza->id,
                    'refaccion_id' => $pieza->id,
                    'nombre' => $pieza->nombre_pieza,
                    'cantidad' => (int) $pieza->pivot->cantidad,
                    'precio' => (float) $pieza->pivot->precio_fijado,
                ];
            })
            ->values()
            ->all();
    }

    protected function buildRefaccionesRegistro(OrdenServicio $orden): array
    {
        return $orden->repuestos
            ->map(function (Inventario $pieza) {
                return [
                    'id' => $pieza->id,
                    'nombre' => $pieza->nombre_pieza,
                    'cantidad' => (int) $pieza->pivot->cantidad,
                    'observacion' => $pieza->sku,
                ];
            })
            ->values()
            ->all();
    }

    protected function guardarFotos(Request $request, OrdenServicio $orden, string $momento): void
    {
        if (!$request->hasFile('fotos')) {
            return;
        }

        foreach ($request->file('fotos') as $foto) {
            $path = $foto->store('evidencias', 'public');

            Evidencia::create([
                'orden_servicio_id' => $orden->id,
                'url_foto' => $path,
                'momento' => $momento,
            ]);
        }
    }

    protected function nombreTecnico(OrdenServicio $orden): string
    {
        $nombre = trim(sprintf(
            '%s %s',
            $orden->user?->nombre ?? '',
            $orden->user?->apellido ?? ''
        ));

        return $nombre !== '' ? $nombre : ($orden->user?->email ?? 'Tecnico asignado');
    }

    protected function mapPrioridad(string $estado): string
    {
        return match ($estado) {
            'recibido' => 'alta',
            'diagnostico', 'espera', 'reparacion' => 'media',
            default => 'baja',
        };
    }
}
