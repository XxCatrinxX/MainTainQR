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

class OrdenServicioController extends Controller
{
    public function index()
    {
        $ordenes = OrdenServicio::with(['equipo.cliente'])->latest()->get();
        return view('ordenes.index', [
            'ordenes'         => $ordenes,
            'totalRecibidas'  => OrdenServicio::where('estado', 'recibido')->count(),
            'totalPendientes' => OrdenServicio::whereIn('estado', ['diagnostico', 'espera'])->count(),
            'totalProceso'    => OrdenServicio::where('estado', 'reparacion')->count(),
            'totalCerradas'   => OrdenServicio::whereIn('estado', ['listo', 'entregado'])->count(),
            'chartData' => [
                'abiertas' => OrdenServicio::whereIn('estado', ['recibido', 'diagnostico', 'espera', 'reparacion'])->count(),
                'cerradas' => OrdenServicio::whereIn('estado', ['listo', 'entregado'])->count(),
            ]
        ]);
    }

    public function show($id)
    {
        $orden = OrdenServicio::with(['equipo.cliente', 'user', 'evidencias', 'repuestos', 'pagos'])->findOrFail($id);
        $inventario = Inventario::where('stock', '>', 0)->get();
        return view('ordenes.show', compact('orden', 'inventario'));
    }

    public function storeDiagnostico(Request $request, $id)
    {
        $request->validate([
            'solucion_propuesta' => 'required|string',
            'mano_obra'          => 'required|numeric|min:0',
            'fotos'              => 'nullable|array',
            'fotos.*'            => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $orden = OrdenServicio::findOrFail($id);
        $orden->solucion_propuesta = $request->solucion_propuesta;
        $orden->mano_obra = $request->mano_obra;
        if (in_array($orden->estado, ['recibido', 'diagnostico'])) {
            $orden->estado = 'espera';
        }
        $orden->save();

        // Guardar/Actualizar en detalles_tecnicos
        DetalleTecnico::updateOrCreate(
            ['orden_servicio_id' => $orden->id],
            ['solucion_propuesta' => $request->solucion_propuesta]
        );

        // Sincronizar Repuestos (Pivot orden_repuestos)
        if ($request->has('repuestos')) {
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

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('evidencias', 'public');
                Evidencia::create(['orden_servicio_id' => $orden->id, 'url_foto' => $path, 'momento' => 'diagnostico']);
            }
        }

        // Enviar correo de diagnóstico al cliente (si tiene correo registrado)
        $orden->load(['equipo.cliente', 'evidencias', 'repuestos']);
        $correoCliente = $orden->equipo->cliente->correo ?? null;
        if ($correoCliente) {
            \Illuminate\Support\Facades\Log::info('DiagnosticoNotificacion: intentando enviar correo', [
                'orden_id' => $orden->id,
                'folio'    => $orden->folio,
                'correo'   => $correoCliente,
            ]);
            try {
                Notification::route('mail', $correoCliente)
                    ->notify(new DiagnosticoNotificacion($orden));
                \Illuminate\Support\Facades\Log::info('DiagnosticoNotificacion: correo enviado exitosamente', [
                    'orden_id' => $orden->id,
                    'correo'   => $correoCliente,
                ]);
                $msgEmail = ' Se envió el correo de diagnóstico al cliente.';
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('DiagnosticoNotificacion: error al enviar correo', [
                    'orden_id'  => $orden->id,
                    'correo'    => $correoCliente,
                    'error'     => $e->getMessage(),
                    'exception' => $e,
                ]);
                $msgEmail = ' (No se pudo enviar el correo: ' . $e->getMessage() . ')';
            }
        } else {
            $msgEmail = ' El cliente no tiene correo registrado.';
        }

        return redirect()->route('ordenes.show', $id)
            ->with('success', 'Diagnóstico guardado y estado actualizado a En Espera.' . $msgEmail);
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
            'nombre' => 'required',
            'apellido_paterno' => 'required',
            'telefono' => 'required',
        ]);

        $clienteId = session('wizard_cliente_id');

        if ($clienteId) {
            $cliente = Cliente::findOrFail($clienteId);
            $cliente->update($request->all());
        } else {
            $cliente = Cliente::create($request->all());
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
        $usuarios = User::all();

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
}

