<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenServicio;
use App\Models\Equipo;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Inventario;
use App\Models\Evidencia;
use App\Models\DetalleTecnico;
use App\Models\SolicitudCompra;
use App\Notifications\DiagnosticoNotificacion;
use App\Notifications\ListoNotificacion;
use Illuminate\Support\Facades\Notification;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FCMNotification;


class OrdenServicioController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = OrdenServicio::with(['equipo.cliente', 'user']);

        // Si es técnico, solo ve lo asignado a él
        if ($user->rol === 'tecnico') {
            $query->where('user_id', $user->id);
        }

        $ordenes = $query->latest()->get();

        // Para los contadores de métricas también aplicamos el filtro si es técnico
        $baseMetricas = OrdenServicio::query();
        if ($user->rol === 'tecnico') {
            $baseMetricas->where('user_id', $user->id);
        }

        return view('ordenes.index', [
            'ordenes'         => $ordenes,
            'totalRecibidas'  => (clone $baseMetricas)->where('estado', 'recibido')->count(),
            'totalPendientes' => (clone $baseMetricas)->whereIn('estado', ['diagnostico', 'espera'])->count(),
            'totalProceso'    => (clone $baseMetricas)->where('estado', 'reparacion')->count(),
            'totalCerradas'   => (clone $baseMetricas)->whereIn('estado', ['listo', 'entregado'])->count(),
            'chartData' => [
                'abiertas' => (clone $baseMetricas)->whereIn('estado', ['recibido', 'diagnostico', 'espera', 'reparacion'])->count(),
                'cerradas' => (clone $baseMetricas)->whereIn('estado', ['listo', 'entregado'])->count(),
            ]
        ]);
    }

    public function show($id)
    {
        $orden = OrdenServicio::with(['equipo.cliente', 'user', 'evidencias', 'repuestos', 'pagos', 'detallesTecnicos'])->findOrFail($id);

        $user = \Illuminate\Support\Facades\Auth::user();
        
        // El almacenista no puede entrar al detalle
        if ($user->rol === 'almacenista') {
            abort(403, 'El almacenista no tiene permiso para ver detalles de órdenes.');
        }

        // Technicians can only see their own assigned orders
        if ($user->rol === 'tecnico' && $orden->user_id !== $user->id) {
            abort(403, 'No tienes acceso a esta orden de servicio.');
        }

        $inventario = Inventario::where('stock', '>', 0)->get();
        return view('ordenes.show', compact('orden', 'inventario'));
    }

  public function storeDiagnostico(Request $request, $id)
{
    if ($request->has('es_reparable')) {
        $request->merge([
            'es_reparable' => filter_var(
                $request->input('es_reparable'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ),
        ]);
    }

    $request->validate([
        'solucion_propuesta'   => 'required|string',
        'es_reparable'         => 'required|boolean',
        'mano_obra'            => 'required|numeric|min:0',
        'monto_compra_piezas'  => 'nullable|numeric|min:0',
        'fotos'                => 'nullable|array',
        'fotos.*'              => 'image|mimes:jpeg,png,jpg,gif|max:5120',

        'repuestos'            => 'nullable|array',
        'repuestos.*.id'       => 'required_with:repuestos|exists:inventario,id',
        'repuestos.*.cantidad' => 'required_with:repuestos|integer|min:1',
        'repuestos.*.precio'   => 'required_with:repuestos|numeric|min:0',
    ]);

    $esReparable = $request->boolean('es_reparable');
    $ofrecerCompra = $request->boolean('ofrecer_compra');

    if (!$esReparable && $ofrecerCompra && !$request->filled('monto_compra_piezas')) {
        return back()
            ->withErrors([
                'monto_compra_piezas' => 'Debes capturar el monto de compra si deseas ofrecerla.'
            ])
            ->withInput();
    }

    $orden = OrdenServicio::findOrFail($id);

    $orden->solucion_propuesta = $request->solucion_propuesta;
    $orden->es_reparable = $esReparable;
    $orden->ofrecer_compra = $esReparable ? false : $ofrecerCompra;

    // Si no es reparable y no lo compramos, cobramos 100 de diagnóstico.
    // Si sí es reparable, tomamos el monto capturado (presupuesto).
    if (!$esReparable && !$ofrecerCompra) {
        $orden->mano_obra = 100;
    } else {
        $orden->mano_obra = $request->mano_obra;
    }

    $orden->monto_compra_piezas = (!$esReparable && $ofrecerCompra)
        ? $request->monto_compra_piezas
        : null;

    if (in_array($orden->estado, ['recibido', 'diagnostico'])) {
        if (!$esReparable && !$ofrecerCompra) {
            $orden->estado = 'listo';
            $orden->fecha_diagnostico = now();
            $orden->fecha_listo = now();
        } else {
            $orden->estado = 'espera';
            $orden->fecha_diagnostico = now();
        }
    }

    $orden->save();

    DetalleTecnico::updateOrCreate(
        ['orden_servicio_id' => $orden->id],
        ['solucion_propuesta' => $request->solucion_propuesta]
    );

    if ($esReparable) {
        if ($request->has('repuestos') && is_array($request->repuestos) && count($request->repuestos) > 0) {
            $repuestosData = [];

            foreach ($request->repuestos as $r) {
                $repuestosData[$r['id']] = [
                    'cantidad' => $r['cantidad'],
                    'precio_fijado' => $r['precio']
                ];
            }

            $orden->repuestos()->sync($repuestosData);
        } else {
            $orden->repuestos()->detach();
        }
    } else {
        $orden->repuestos()->detach();
    }

    if ($request->hasFile('fotos')) {
        foreach ($request->file('fotos') as $foto) {
            $path = $foto->store('evidencias', 'public');

            Evidencia::create([
                'orden_servicio_id' => $orden->id,
                'url_foto' => $path,
                'momento' => 'diagnostico'
            ]);
        }
    }

    $orden->load(['equipo.cliente', 'evidencias', 'repuestos']);

    $correoCliente = $orden->equipo->cliente->correo ?? null;
    $msgEmail = ' El cliente no tiene correo registrado.';

    if ($correoCliente) {
        try {
            Notification::route('mail', $correoCliente)
                ->notify(new DiagnosticoNotificacion($orden));

            $msgEmail = ' Se envió el correo de diagnóstico al cliente.';
        } catch (\Exception $e) {
            $msgEmail = ' (No se pudo enviar el correo: ' . $e->getMessage() . ')';
        }
    }

    return redirect()->route('ordenes.show', $id)
        ->with('success', 'Diagnóstico guardado y estado actualizado a En Espera.' . $msgEmail);
}


    public function storeDiagnosticoApi(Request $request, $id)
{
    if ($request->has('es_reparable')) {
        $request->merge([
            'es_reparable' => filter_var(
                $request->input('es_reparable'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ),
        ]);
    }

    $request->validate([
        'solucion_propuesta'   => 'required|string',
        'es_reparable'         => 'required|boolean',
        'mano_obra'            => 'required|numeric|min:0',
        'monto_compra_piezas'  => 'nullable|numeric|min:0',

        'repuestos'            => 'nullable|array',
        'repuestos.*.id'       => 'required_with:repuestos|exists:inventario,id',
        'repuestos.*.cantidad' => 'required_with:repuestos|integer|min:1',
        'repuestos.*.precio'   => 'required_with:repuestos|numeric|min:0',

        'fotos'                => 'nullable|array',
        'fotos.*'              => 'image|mimes:jpeg,png,jpg,gif|max:5120',
    ]);

    $esReparable = $request->boolean('es_reparable');
    $ofrecerCompra = $request->boolean('ofrecer_compra');

    if (!$esReparable && $ofrecerCompra && !$request->filled('monto_compra_piezas')) {
        return response()->json([
            'success' => false,
            'message' => 'Debes capturar el monto de compra si deseas ofrecerla.'
        ], 422);
    }

    $orden = OrdenServicio::findOrFail($id);

    $orden->solucion_propuesta = $request->solucion_propuesta;
    $orden->es_reparable = $esReparable;
    $orden->ofrecer_compra = $esReparable ? false : $ofrecerCompra;

    // Si no es reparable y no lo compramos, cobramos 100 de diagnóstico.
    if (!$esReparable && !$ofrecerCompra) {
        $orden->mano_obra = 100;
    } else {
        $orden->mano_obra = $request->mano_obra;
    }

    $orden->monto_compra_piezas = (!$esReparable && $ofrecerCompra)
        ? $request->monto_compra_piezas
        : null;

    if (in_array($orden->estado, ['recibido', 'diagnostico'])) {
        if (!$esReparable && !$ofrecerCompra) {
            $orden->estado = 'listo';
            $orden->fecha_diagnostico = now();
            $orden->fecha_listo = now();
        } else {
            $orden->estado = 'espera';
            $orden->fecha_diagnostico = now();
        }
    }

    $orden->save();

    DetalleTecnico::updateOrCreate(
        ['orden_servicio_id' => $orden->id],
        ['solucion_propuesta' => $request->solucion_propuesta]
    );

    if ($esReparable) {
        if ($request->has('repuestos') && is_array($request->repuestos) && count($request->repuestos) > 0) {
            $repuestosData = [];

            foreach ($request->repuestos as $r) {
                $repuestosData[$r['id']] = [
                    'cantidad' => $r['cantidad'],
                    'precio_fijado' => $r['precio']
                ];
            }

            $orden->repuestos()->sync($repuestosData);
        } else {
            $orden->repuestos()->detach();
        }
    } else {
        $orden->repuestos()->detach();
    }

    if ($request->hasFile('fotos')) {
        foreach ($request->file('fotos') as $foto) {
            $path = $foto->store('evidencias', 'public');

            Evidencia::create([
                'orden_servicio_id' => $orden->id,
                'url_foto' => $path,
                'momento' => 'diagnostico'
            ]);
        }
    }

    $orden->load(['equipo.cliente', 'evidencias', 'repuestos']);

    $correoCliente = $orden->equipo->cliente->correo ?? null;
    $correoEnviado = false;
    $errorCorreo = null;

    if ($correoCliente) {
        try {
            Notification::route('mail', $correoCliente)
                ->notify(new DiagnosticoNotificacion($orden));
            $correoEnviado = true;
        } catch (\Exception $e) {
            $errorCorreo = $e->getMessage();
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Diagnóstico guardado correctamente',
        'orden_id' => $orden->id,
        'folio' => $orden->folio,
        'estado' => $orden->estado,
        'es_reparable' => $orden->es_reparable,
        'monto_compra_piezas' => $orden->monto_compra_piezas,
        'correo_enviado' => $correoEnviado,
        'error_correo' => $errorCorreo,
    ], 200);
}

    // ==========================================
    // PASO 1: CLIENTE
    // ==========================================
    public function createPaso1()
    {
        $clienteId = session('wizard_cliente_id');
        $cliente = $clienteId ? Cliente::find($clienteId) : null;

        return view('ordenes.wizard.paso1', compact('cliente'));
    }

    public function storePaso1(Request $request)
    {
        $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'telefono'         => 'required|string|max:20',
            'correo'           => 'nullable|email|max:255',
            'direccion'        => 'nullable|string|max:255',
        ]);

        $clienteId = session('wizard_cliente_id');

        if ($clienteId && Cliente::where('id', $clienteId)->exists()) {
            // Editing an existing client already in the session — update it
            $cliente = Cliente::findOrFail($clienteId);
            $cliente->update($request->only([
                'nombre', 'apellido_paterno', 'apellido_materno',
                'telefono', 'correo', 'direccion'
            ]));
        } else {
            // Find by email (unique key) or phone, update if found, create if not
            $searchKey = $request->filled('correo')
                ? ['correo' => $request->correo]
                : ['telefono' => $request->telefono];

            $cliente = Cliente::updateOrCreate(
                $searchKey,
                $request->only([
                    'nombre', 'apellido_paterno', 'apellido_materno',
                    'telefono', 'correo', 'direccion'
                ])
            );

            session(['wizard_cliente_id' => $cliente->id]);
        }

        return redirect()->route('ordenes.create_paso2');
    }

    // ==========================================
    // PASO 2: EQUIPO
    // ==========================================
    public function createPaso2()
    {
        if (!session()->has('wizard_cliente_id')) {
            return redirect()->route('ordenes.create_paso1')->with('error', 'Primero registre el cliente.');
        }

        $equipoId = session('wizard_equipo_id');
        $equipo = $equipoId ? Equipo::find($equipoId) : null;

        return view('ordenes.wizard.paso2', compact('equipo'));
    }

    public function storePaso2(Request $request)
    {
        $request->validate([
            'tipo' => 'required',
            'marca' => 'required',
        ]);

        $clienteId = session('wizard_cliente_id');
        $equipoId = session('wizard_equipo_id');

        $data = $request->all();
        $data['cliente_id'] = $clienteId;

        if ($equipoId) {
            $equipo = Equipo::findOrFail($equipoId);
            $equipo->update($data);
        } else {
            $data['numero_serie'] = $data['numero_serie'] ?? uniqid();
            $data['qr_token'] = uniqid('qr_');
            $equipo = Equipo::create($data);
            session(['wizard_equipo_id' => $equipo->id]);
        }

        return redirect()->route('ordenes.create_paso3');
    }

    // ==========================================
    // PASO 3: ORDEN (RESUMEN)
    // ==========================================
    public function createPaso3()
    {
        if (!session()->has('wizard_cliente_id') || !session()->has('wizard_equipo_id')) {
            return redirect()->route('ordenes.create_paso1')->with('error', 'Complete los pasos previos.');
        }

        $cliente = Cliente::findOrFail(session('wizard_cliente_id'));
        $equipo = Equipo::findOrFail(session('wizard_equipo_id'));
        $usuarios = User::where('rol', 'tecnico')->get();

        return view('ordenes.wizard.paso3', compact('cliente', 'equipo', 'usuarios'));
    }

    public function storePaso3(Request $request)
    {
        $request->validate([
            'problema_reportado' => 'required',
            'estado_fisico' => 'required',
            'id_usuario' => 'required|exists:users,id'
        ]);

        $clienteId = session('wizard_cliente_id');
        $equipoId = session('wizard_equipo_id');

        if (!$clienteId || !$equipoId) {
            return redirect()->route('ordenes.create_paso1')->with('error', 'Faltan datos del cliente o equipo.');
        }

        $anio = date('Y');
        $siguienteId = OrdenServicio::max('id') + 1;
        $folio = 'OS-' . $anio . '-' . str_pad($siguienteId, 4, '0', STR_PAD_LEFT);

        $orden = OrdenServicio::create([
            'folio' => $folio,
            'equipo_id' => $equipoId,
            'user_id' => $request->id_usuario,
            'falla_reportada' => $request->problema_reportado,
            'estado_fisico' => $request->estado_fisico,
            'estado' => 'recibido',
            'token_rastreo' => uniqid('rastreo_'),
        ]);

       
        $tecnico = User::find($request->id_usuario);

if ($tecnico && $tecnico->fcm_token) {
    try {
        $messaging = app('firebase.messaging');
        
        // Usamos el alias para la notificación "Push"
        $fcmNotification = FCMNotification::create(
            'Nueva Orden Asignada', 
            "Folio: {$orden->folio}. Tienes un nuevo equipo para revisar."
        );

        $message = CloudMessage::withTarget('token', $tecnico->fcm_token)
            ->withNotification($fcmNotification)
            ->withData([
                'id_orden' => (string)$orden->id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK' // Útil si usas Flutter/Android
            ]);

        $messaging->send($message);
        
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error("Error FCM: " . $e->getMessage());
    }
}

        // Limpiar sesión del wizard
        session()->forget(['wizard_cliente_id', 'wizard_equipo_id']);

        return redirect()->route('ordenes.recepcion', $orden->id)
            ->with('success', 'Orden de Servicio creada correctamente');
    }

    // ==========================================
    // CIERRE Y RECIBO
    // ==========================================
    public function showRecepcion($id)
    {
        $orden = OrdenServicio::with('equipo.cliente')->findOrFail($id);

        $qrCode = new \Endroid\QrCode\QrCode(
            data: $orden->equipo->qr_token,
            encoding: new Encoding('UTF-8'),
            size: 200,
            margin: 10
        );
        $writer = new SvgWriter();
        $result = $writer->write($qrCode);

        $qrBase64 = base64_encode($result->getString());

        return view('ordenes.recepcion', compact('orden', 'qrBase64'));
    }

    public function storeDetalle(Request $request, $id)
    {
        $request->validate([
            'trabajo_finalizado' => 'required|string',
            'observaciones_internas' => 'nullable|string',
        ]);

        $orden = OrdenServicio::with('equipo.cliente')->findOrFail($id);
        
        DetalleTecnico::updateOrCreate(
            ['orden_servicio_id' => $orden->id],
            [
                'trabajo_finalizado' => $request->trabajo_finalizado,
                'observaciones_internas' => $request->observaciones_internas,
            ]
        );

        // Avanzar estado a LISTO
        $orden->estado = 'listo';
        $orden->fecha_listo = now();
        $orden->save();

        // Notificar al cliente
        $correoCliente = $orden->equipo->cliente->correo ?? null;
        if ($correoCliente) {
            \Illuminate\Support\Facades\Log::info('ListoNotificacion (storeDetalle): intentando enviar correo', [
                'orden_id' => $orden->id,
                'folio'    => $orden->folio,
                'correo'   => $correoCliente,
            ]);
            try {
                \Illuminate\Support\Facades\Notification::route('mail', $correoCliente)
                    ->notify(new ListoNotificacion($orden));
                \Illuminate\Support\Facades\Log::info('ListoNotificacion (storeDetalle): correo enviado exitosamente', [
                    'orden_id' => $orden->id,
                    'correo'   => $correoCliente,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('ListoNotificacion (storeDetalle): error al enviar correo', [
                    'orden_id'  => $orden->id,
                    'correo'    => $correoCliente,
                    'error'     => $e->getMessage(),
                    'exception' => $e,
                ]);
            }
        }

        return redirect()->route('ordenes.show', $id)
            ->with('success', 'Reparación finalizada. Estado actualizado a Listo y cliente notificado.');
    }

    public function update(Request $request, $id)
    {
        $orden = OrdenServicio::with('repuestos', 'pagos', 'equipo.cliente')->findOrFail($id);

        if ($request->input('estado') === 'entregado') {
            $totalPagar = $orden->mano_obra + $orden->repuestos->sum(function($r) {
                return $r->pivot->cantidad * $r->pivot->precio_fijado;
            });
            $totalPagado = $orden->pagos->sum('monto');

            if ($totalPagado < $totalPagar) {
                return back()->with('error', 'No se puede Entregar. Existe un saldo pendiente de $' . number_format($totalPagar - $totalPagado, 2));
            }
            
            $orden->fecha_entrega_real = now();
        }

        $estadoAnterior = $orden->estado;
        $orden->update($request->only('estado', 'fecha_estimada_entrega'));

        // Enviar notificación si el estado cambió a listo
        if ($estadoAnterior !== $orden->estado && $orden->estado === 'listo') {
            $correoCliente = $orden->equipo->cliente->correo;
            if (!empty($correoCliente)) {
                \Illuminate\Support\Facades\Log::info('ListoNotificacion (update): intentando enviar correo', [
                    'orden_id' => $orden->id,
                    'folio'    => $orden->folio,
                    'correo'   => $correoCliente,
                ]);
                try {
                    \Illuminate\Support\Facades\Notification::route('mail', $correoCliente)
                        ->notify(new \App\Notifications\ListoNotificacion($orden));
                    \Illuminate\Support\Facades\Log::info('ListoNotificacion (update): correo enviado exitosamente', [
                        'orden_id' => $orden->id,
                        'correo'   => $correoCliente,
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('ListoNotificacion (update): error al enviar correo', [
                        'orden_id'  => $orden->id,
                        'correo'    => $correoCliente,
                        'error'     => $e->getMessage(),
                        'exception' => $e,
                    ]);
                }
            }
        }

        return back()->with('success', 'Estado de la orden actualizado exitosamente.');
    }

    // ==========================================
    // STATE MACHINE — Explicit Transitions
    // ==========================================

    /** recibido → diagnostico  (técnico confirma que la recibió) */
    public function confirmarRecepcion($id)
    {
        $orden = OrdenServicio::findOrFail($id);
        if ($orden->estado !== 'recibido') {
            return back()->with('error', 'La orden ya no está en estado Recibido.');
        }
        
        // Solo admin y técnicos pueden confirmar recepción técnica
        if (!in_array(Auth::user()->rol, ['admin', 'tecnico'])) {
            abort(403, 'No tienes permiso para confirmar recepción.');
        }

        $orden->estado = 'diagnostico';
        $orden->save();
        return back()->with('success', 'Recepción confirmada. La orden pasó a estado Diagnóstico.');
    }

    /** aceptado → reparacion  (técnico inicia la reparación) */
    public function iniciarReparacion($id)
    {
        $orden = OrdenServicio::findOrFail($id);
        if ($orden->estado !== 'aceptado') {
            return back()->with('error', 'La orden debe estar Aprobada para iniciar reparación.');
        }

        // Solo admin y técnicos pueden iniciar reparación
        if (!in_array(Auth::user()->rol, ['admin', 'tecnico'])) {
            abort(403, 'No tienes permiso para iniciar la reparación.');
        }

        $orden->estado = 'reparacion';
        $orden->fecha_reparacion = now();
        $orden->save();
        return back()->with('success', 'Reparación iniciada. La orden pasó a estado En Reparación.');
    }

    /** rechazado → entregado  (técnico cierra y devuelve el equipo) */
    public function cerrarRechazada($id)
    {
        $orden = OrdenServicio::findOrFail($id);
        if ($orden->estado !== 'rechazado') {
            return back()->with('error', 'La orden debe estar en estado Rechazado para cerrarse.');
        }
        
        if (!in_array(Auth::user()->rol, ['admin', 'recepcionista'])) {
            return back()->with('error', 'Solo el recepcionista o administrador puede realizar la devolución al cliente.');
        }

        $orden->estado = 'entregado';
        $orden->fecha_entrega_real = now();
        $orden->save();
        return back()->with('success', 'Equipo devuelto. La orden fue cerrada.');
    }

    /** listo → entregado  (recepcionista entrega con pago completo) */
    public function confirmarEntrega($id)
    {
        $orden = OrdenServicio::with('repuestos', 'pagos')->findOrFail($id);
        if ($orden->estado !== 'listo') {
            return back()->with('error', 'La orden debe estar en estado Listo para confirmar entrega.');
        }

        if (!in_array(Auth::user()->rol, ['admin', 'recepcionista'])) {
            return back()->with('error', 'Solo el recepcionista o administrador puede entregar o cerrar la compra de la orden.');
        }

        if ($orden->ofrecer_compra) {
            $totalDebe = $orden->monto_compra_piezas ?? 0;
            $totalPagado = $orden->pagos->sum('monto');
            if ($totalPagado < $totalDebe) {
                return back()->with('error', 'No se puede cerrar. Saldo pendiente de pago al cliente: $' . number_format($totalDebe - $totalPagado, 2));
            }
        } else {
            $totalDebe  = ($orden->mano_obra ?? 0) + $orden->repuestos->sum(fn($r) => $r->pivot->cantidad * $r->pivot->precio_fijado);
            $totalPagado = $orden->pagos->sum('monto');

            if ($totalPagado < $totalDebe) {
                return back()->with('error', 'No se puede entregar. Saldo pendiente del cliente: $' . number_format($totalDebe - $totalPagado, 2));
            }
        }

        $orden->estado = 'entregado';
        $orden->fecha_entrega_real = now();
        $orden->save();
        return back()->with('success', 'Operación completada y orden cerrada exitosamente.');
    }

    public function verQR($id)
{
    $orden = OrdenServicio::with('equipo.cliente')->findOrFail($id);

    $qrCode = new \Endroid\QrCode\QrCode(
        data: $orden->equipo->qr_token,
        encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
        size: 200,
        margin: 10
    );

    $writer = new \Endroid\QrCode\Writer\SvgWriter();
    $result = $writer->write($qrCode);

    $qrBase64 = base64_encode($result->getString());

    return view('ordenes.qr', compact('orden', 'qrBase64'));
}


}

