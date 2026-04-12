@extends('adminlte::page')

@section('title', 'Orden ' . $orden->folio)

@section('css')
    {{-- Estilos centralizados en admin-custom.css --}}
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Orden <span style="font-family: monospace;">{{ $orden->folio }}</span></h1>
            <a href="{{ route('ordenes.index') }}" class="text-muted" style="font-size: 0.85rem; text-decoration: none;">
                <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
            </a>
        </div>
        @php
            $badgeMap = [
                'recibido'   => 'secondary',
                'diagnostico'=> 'warning',
                'espera'     => 'info',
                'rechazado'  => 'danger',
                'reparacion' => 'primary',
                'para_pzas'  => 'olive',
                'listo'      => 'success',
                'entregado'  => 'dark',
            ];

            $labelMap = [
                'recibido'   => 'Recibido',
                'diagnostico'=> 'En Diagnóstico',
                'espera'     => 'Esperando Respuesta',
                'rechazado'  => 'Rechazado',
                'reparacion' => 'En Reparación',
                'para_pzas'  => 'Para Piezas',
                'listo'      => 'Listo para Entrega',
                'entregado'  => 'Entregado',
            ];
        @endphp
        <span class="badge badge-{{ $badgeMap[$orden->estado] ?? 'secondary' }}" style="font-size: 0.9rem; padding: 0.5em 1.2em;">
            {{ $labelMap[$orden->estado] ?? $orden->estado }}
        </span>
    </div>
@stop

@section('content')

@php
    $esReparableOrden = is_null($orden->es_reparable) ? true : (bool) $orden->es_reparable;
@endphp

{{-- ALERTS --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:8px;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius:8px;">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius:8px;">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Corrige lo siguiente:</strong>
        <ul class="mb-0 mt-2 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- STEP PROGRESS BAR --}}
@php
    if ($esReparableOrden) {
        $allStates = ['recibido','diagnostico','espera','reparacion','listo','entregado'];
        $stepLabels = [
            'recibido'   => 'Recibido',
            'diagnostico'=> 'Diagnóstico',
            'espera'     => 'En Espera',
            'reparacion' => 'Reparación',
            'listo'      => 'Listo',
            'entregado'  => 'Entregado'
        ];
    } else {
        $allStates = ['recibido','diagnostico','espera','para_pzas','entregado'];
        $stepLabels = [
            'recibido'   => 'Recibido',
            'diagnostico'=> 'Diagnóstico',
            'espera'     => 'En Espera',
            'para_pzas'  => 'Para Piezas',
            'entregado'  => 'Entregado'
        ];
    }

    $estadoProgreso = $orden->estado;
    $currentPos = array_search($estadoProgreso, $allStates);
    if ($currentPos === false) $currentPos = 0;
@endphp

<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <div class="d-flex align-items-center" style="gap: 0;">
            @foreach($allStates as $i => $s)
                @php
                    $pos = array_search($s, $allStates);
                    $isDone   = $pos < $currentPos;
                    $isActive = $pos === $currentPos;
                @endphp
                <div class="d-flex align-items-center" style="flex: 1;">
                    <div class="d-flex flex-column align-items-center" style="flex: 0;">
                        <div style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.75rem;
                            background: {{ $isActive ? '#111827' : ($isDone ? '#10b981' : '#e5e7eb') }};
                            color: {{ ($isActive || $isDone) ? 'white' : '#9ca3af' }};">
                            @if($isDone)
                                <i class="fas fa-check" style="font-size:0.65rem;"></i>
                            @else
                                {{ $i + 1 }}
                            @endif
                        </div>
                        <div style="font-size:0.65rem; font-weight:{{ $isActive ? '700' : '500' }}; color:{{ $isActive ? '#111827' : ($isDone ? '#10b981' : '#9ca3af') }}; margin-top:4px; text-align:center; white-space:nowrap;">
                            {{ $stepLabels[$s] }}
                        </div>
                    </div>
                    @if(!$loop->last)
                        <div style="flex:1; height:2px; background: {{ $isDone ? '#10b981' : '#e5e7eb' }}; margin: 0 4px 20px;"></div>
                    @endif
                </div>
            @endforeach
        </div>

        @if($orden->estado === 'rechazado')
            <div class="text-center mt-2">
                <span class="badge badge-danger" style="font-size:0.8rem; padding: 0.4em 1em;">
                    <i class="fas fa-times-circle mr-1"></i>
                    {{ $esReparableOrden ? 'Cliente rechazó la reparación' : 'Cliente rechazó la oferta por piezas' }}
                </span>
            </div>
        @elseif($orden->estado === 'para_pzas')
            <div class="text-center mt-2">
                <span class="badge badge-olive" style="font-size:0.8rem; padding: 0.4em 1em;">
                    <i class="fas fa-check-circle mr-1"></i> Cliente aceptó oferta por piezas
                </span>
            </div>
        @endif
    </div>
</div>

<div class="row">
    {{-- ===================== COLUMNA IZQUIERDA: Panel de Estado ===================== --}}
    <div class="col-md-8">

        @if($orden->estado === 'recibido')
            {{-- ─── PANEL: RECIBIDO ─── --}}
            <div class="card" style="border-left: 4px solid #6b7280;">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-inbox mr-2 text-secondary"></i>Paso 1: Recepción del Equipo</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">La orden ha sido creada y está lista para ser procesada.</p>

                    <div class="p-3 rounded mb-4" style="background:#f8fafc; border: 1px solid #e5e7eb;">
                        <div class="row">
                            <div class="col-md-6"><strong>Falla Reportada:</strong><br><span class="text-muted">{{ $orden->falla_reportada }}</span></div>
                            <div class="col-md-6"><strong>Estado Físico:</strong><br><span class="text-muted">{{ $orden->estado_fisico }}</span></div>
                        </div>
                    </div>

                    <div class="text-center p-4 rounded" style="background: #fffbeb; border: 1px dashed #f59e0b;">
                        <div class="mb-3">
                            <i class="fas fa-mobile-alt fa-2x text-warning mb-2"></i>
                            <div class="spinner-grow spinner-grow-sm text-warning" role="status"></div>
                        </div>
                        <h6 class="font-weight-bold text-warning mb-1">Esperando respuesta del técnico</h6>
                        <p class="small text-muted mb-0">
                            El técnico <strong>{{ $orden->user->nombre ?? 'asignado' }}</strong> debe confirmar la recepción física desde la aplicación móvil para habilitar el diagnóstico.
                        </p>
                    </div>
                </div>
            </div>

        @elseif($orden->estado === 'diagnostico')
{{-- ─── PANEL: DIAGNÓSTICO ─── --}}
<div class="card" style="border-left: 4px solid #f59e0b;">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-stethoscope mr-2 text-warning"></i>Paso 2: Diagnóstico Técnico</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('ordenes.diagnostico', $orden->id) }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group mb-3">
                <label>Solución Propuesta *</label>
                <textarea
                    name="solucion_propuesta"
                    class="form-control"
                    rows="4"
                    placeholder="Describe el diagnóstico y la solución propuesta..."
                    required>{{ old('solucion_propuesta', $orden->solucion_propuesta) }}</textarea>
            </div>

            @php
                $esReparableOld = old('es_reparable', is_null($orden->es_reparable) ? '1' : ($orden->es_reparable ? '1' : '0'));
            @endphp

            <div class="form-group mb-3">
                <label>Resultado de la revisión *</label>
                <select name="es_reparable" id="es_reparable" class="custom-select" required>
                    <option value="1" {{ $esReparableOld === '1' ? 'selected' : '' }}>Sí, el equipo es reparable</option>
                    <option value="0" {{ $esReparableOld === '0' ? 'selected' : '' }}>No, ofrecer compra para piezas</option>
                </select>
                <small class="text-muted">
                    Si el equipo no tiene reparación, podrás enviar una propuesta de compra para piezas al cliente.
                </small>
            </div>

            <div id="bloque-reparable">
                <div class="form-group mb-3">
                    <label>Costo de Mano de Obra *</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                        <input
                            type="number"
                            name="mano_obra"
                            id="mano_obra"
                            class="form-control"
                            step="0.01"
                            min="0"
                            value="{{ old('mano_obra', $orden->mano_obra ?? 0) }}"
                            placeholder="0.00">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label>Refacciones Necesarias</label>
                    <div class="row mb-2">
                        <div class="col-md-7">
                            <select id="select-repuesto" class="custom-select">
                                <option value="">-- Seleccionar pieza del inventario --</option>
                                @foreach($inventario as $pieza)
                                    <option
                                        value="{{ $pieza->id }}"
                                        data-precio="{{ $pieza->precio_venta }}"
                                        data-nombre="{{ $pieza->nombre_pieza }}"
                                        data-stock="{{ $pieza->stock }}">
                                        {{ $pieza->nombre_pieza }} (Stock: {{ $pieza->stock }}) - ${{ number_format($pieza->precio_venta, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" id="cant-repuesto" class="form-control" value="1" min="1" placeholder="Cant.">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn-add-repuesto" class="btn btn-secondary btn-block">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="tabla-repuestos-diagnosis">
                            <thead class="bg-light">
                                <tr>
                                    <th>Pieza</th>
                                    <th>Cant.</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total Piezas:</th>
                                    <th id="total-piezas-diag">$0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>


            <div id="bloque-no-reparable" style="display:none;">
                            <div class="form-group mb-3">
                                <label>Monto de compra para piezas *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="monto_compra_piezas"
                                        id="monto_compra_piezas"
                                        class="form-control"
                                        value="{{ old('monto_compra_piezas', $orden->monto_compra_piezas) }}"
                                        placeholder="0.00">
                                </div>
                                <small class="text-muted">
                                    Este será el monto que verá el cliente como oferta por su equipo para uso en piezas.
                                </small>
                            </div>
                        </div>

                        {{-- EVIDENCIAS --}}
                        <div class="form-group mb-3">
                            <label>Evidencia Fotográfica (opcional)</label>
                            <input type="file" name="fotos[]" class="form-control" multiple accept="image/*" style="padding:0.4rem;">
                            <small class="text-muted">JPG, PNG, GIF. Máx 5 MB c/u.</small>
                        </div>

                        <div id="hidden-inputs-repuestos"></div>

                        <button type="submit" class="btn btn-warning btn-block py-3" style="color:#fff; font-size:1rem;">
                            <i class="fas fa-paper-plane mr-2"></i> Guardar Diagnóstico y Notificar al Cliente
                        </button>
                    </form>

                    {{-- SOLICITUD DE COMPRA FUERA DEL FORM PRINCIPAL --}}
                    <div id="solicitud-compra-panel">
                        <hr class="my-4">
                        <div class="bg-light p-3 rounded" style="border:1px dashed #ced4da;">
                            <h6 class="mb-3" style="font-weight:700; color:#4b5563;">
                                <i class="fas fa-shopping-cart mr-2"></i>¿Falta una pieza? Solicitar Compra
                            </h6>
                            <form action="{{ route('solicitudes.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="orden_servicio_id" value="{{ $orden->id }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" name="nombre_pieza" class="form-control form-control-sm mb-2" placeholder="Nombre de la pieza..." required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="cantidad" class="form-control form-control-sm mb-2" value="1" min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm btn-block">Solicitar</button>
                                    </div>
                                    <div class="col-12">
                                        <textarea name="descripcion" class="form-control form-control-sm" rows="1" placeholder="Detalles extra..."></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($orden->estado === 'espera')
            {{-- ─── PANEL: EN ESPERA ─── --}}
            <div class="card" style="border-left: 4px solid #0ea5e9;">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-hourglass-half mr-2 text-info"></i>
                        Paso 3: Esperando Decisión del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="p-4 rounded mb-4" style="background:#fffbeb; border:1px solid #fde68a;">
                        <p class="mb-2" style="font-weight:600; color:#92400e;">
                            <i class="fas fa-envelope mr-2"></i>
                            {{ $esReparableOrden ? 'Se ha enviado el presupuesto al cliente.' : 'Se ha enviado la propuesta por piezas al cliente.' }}
                        </p>
                        <p class="mb-0 text-muted" style="font-size:0.9rem;">
                            {{ $esReparableOrden
                                ? 'La reparación no puede iniciar hasta que el cliente acepte o rechace el diagnóstico. El estado cambiará automáticamente cuando el cliente responda desde el enlace enviado.'
                                : 'El equipo fue marcado como no reparable. El cliente debe aceptar o rechazar la propuesta por piezas desde el enlace enviado.' }}
                        </p>
                    </div>

                    @if($orden->solucion_propuesta)
                        <div class="p-3 rounded mb-3" style="background:#f8fafc; border:1px solid #e5e7eb;">
                            <strong>{{ $esReparableOrden ? 'Diagnóstico registrado:' : 'Motivo del diagnóstico:' }}</strong><br>
                            <p class="text-muted mt-2 mb-0">{{ $orden->solucion_propuesta }}</p>
                        </div>
                    @endif

                    @if(!$esReparableOrden)
                        <div class="p-3 rounded mb-3" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                            <strong>Monto ofrecido por piezas:</strong><br>
                            <p class="mt-2 mb-0" style="font-size:1.1rem; font-weight:700; color:#166534;">
                                ${{ number_format($orden->monto_compra_piezas ?? 0, 2) }}
                            </p>
                        </div>
                    @endif

                    <div class="d-flex align-items-center p-3 rounded" style="background:#f0f9ff; border:1px solid #bae6fd;">
                        <i class="fas fa-link mr-3 text-info" style="font-size:1.2rem;"></i>
                        <div>
                            <strong style="font-size:0.85rem;">Enlace de rastreo del cliente:</strong><br>
                            <code style="font-size:0.78rem; color:#0369a1;">{{ url('/seguimiento/' . $orden->token_rastreo) }}</code>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($orden->estado === 'rechazado')
            {{-- ─── PANEL: RECHAZADO ─── --}}
            <div class="card" style="border-left: 4px solid #ef4444;">
                <div class="card-header" style="background:#fef2f2;">
                    <h5 class="card-title" style="color:#991b1b;">
                        <i class="fas fa-times-circle mr-2"></i>
                        {{ $esReparableOrden ? 'Cliente Rechazó la Reparación' : 'Cliente Rechazó la Oferta por Piezas' }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        El cliente no aceptó la propuesta. Coordina la devolución del equipo y confirma el cierre de la orden cuando ya haya sido entregado.
                    </p>
                    <div class="p-3 rounded mb-4" style="background:#fef2f2; border:1px solid #fecaca;">
                        <i class="fas fa-info-circle mr-2 text-danger"></i>
                        Al confirmar la devolución, la orden quedará marcada como <strong>Entregada</strong> y se registrará la fecha de salida del equipo.
                    </div>
                    <form method="POST" action="{{ route('ordenes.cerrarRechazada', $orden->id) }}">
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger btn-block py-3"
                            style="font-size:1rem;"
                            onclick="return confirm('¿Confirmas que el equipo fue devuelto al cliente?')">
                            <i class="fas fa-undo mr-2"></i> Confirmar Devolución y Cerrar Orden
                        </button>
                    </form>
                </div>
            </div>

        @elseif($orden->estado === 'para_pzas')
            {{-- ─── PANEL: PARA PIEZAS ─── --}}
            <div class="card" style="border-left: 4px solid #65a30d;">
                <div class="card-header" style="background:#f7fee7;">
                    <h5 class="card-title" style="color:#3f6212;">
                        <i class="fas fa-puzzle-piece mr-2"></i>Proceso Aceptado para Piezas
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        El cliente aceptó la propuesta por su equipo para uso en piezas.
                    </p>

                    <div class="p-3 rounded mb-3" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                        <strong>Monto aceptado:</strong><br>
                        <p class="mt-2 mb-0" style="font-size:1.15rem; font-weight:700; color:#166534;">
                            ${{ number_format($orden->monto_compra_piezas ?? 0, 2) }}
                        </p>
                    </div>

                    @if($orden->solucion_propuesta)
                        <div class="p-3 rounded mb-3" style="background:#f8fafc; border:1px solid #e5e7eb;">
                            <strong>Motivo del diagnóstico:</strong><br>
                            <p class="text-muted mt-2 mb-0">{{ $orden->solucion_propuesta }}</p>
                        </div>
                    @endif

                    <p class="text-muted mb-0">
                        Da seguimiento interno al proceso correspondiente y usa el cambio de estado cuando el equipo ya haya salido del taller.
                    </p>
                </div>
            </div>

        @elseif($orden->estado === 'reparacion')
            {{-- ─── PANEL: EN REPARACIÓN ─── --}}
            <div class="card" style="border-left: 4px solid #3b82f6;">
                <div class="card-header" style="background:#eff6ff;">
                    <h5 class="card-title" style="color:#1d4ed8;"><i class="fas fa-wrench mr-2"></i>Paso 4: Finalizar Reparación</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('ordenes.detalle', $orden->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Trabajo Realizado *</label>
                            <textarea name="trabajo_finalizado" class="form-control" rows="4" required
                                placeholder="Describe detalladamente qué se le hizo al equipo...">{{ old('trabajo_finalizado', $orden->detallesTecnicos->trabajo_finalizado ?? '') }}</textarea>
                        </div>
                        <div class="form-group mb-4">
                            <label>Observaciones Internas <small class="text-muted">(no visibles para el cliente)</small></label>
                            <textarea name="observaciones_internas" class="form-control" rows="2"
                                placeholder="Notas para el equipo técnico...">{{ old('observaciones_internas', $orden->detallesTecnicos->observaciones_internas ?? '') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block py-3" style="font-size:1rem;"
                            onclick="return confirm('¿Finalizar la reparación? El equipo pasará a estado LISTO y se notificará al cliente.')">
                            <i class="fas fa-check-circle mr-2"></i> Finalizar Reparación — Marcar como LISTO
                        </button>
                    </form>
                </div>
            </div>

        @elseif($orden->estado === 'listo')
            {{-- ─── PANEL: LISTO PARA ENTREGA ─── --}}
            <div class="card" style="border-left: 4px solid #10b981;">
                <div class="card-header" style="background:#f0fdf4;">
                    <h5 class="card-title" style="color:#166534;"><i class="fas fa-box-open mr-2"></i>Paso 5: Confirmar Entrega al Cliente</h5>
                </div>
                <div class="card-body">
                    @php
                        $totalDebe   = ($orden->mano_obra ?? 0) + $orden->repuestos->sum(fn($r) => $r->pivot->cantidad * $r->pivot->precio_fijado);
                        $totalPagado = $orden->pagos->sum('monto');
                        $saldo       = $totalDebe - $totalPagado;
                    @endphp

                    @if($saldo > 0)
                        <div class="alert alert-warning" style="border-radius:8px;">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Saldo pendiente: ${{ number_format($saldo, 2) }}</strong> — Registra el cobro antes de confirmar la entrega.
                        </div>
                    @else
                        <div class="alert" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; color:#166534;">
                            <i class="fas fa-check-circle mr-2"></i> Pago completo. El equipo puede ser entregado.
                        </div>
                    @endif

                    {{-- REGISTRO DE COBRO --}}
                    <form method="POST" action="{{ route('ordenes.pago', $orden->id) }}" class="mb-4">
                        @csrf
                        <h6 style="font-weight:700; color:#374151;"><i class="fas fa-dollar-sign mr-2 text-success"></i>Registrar Cobro</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Monto</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" name="monto" class="form-control" step="0.01" min="1" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Método</label>
                                <select name="metodo_pago" class="custom-select">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Tipo</label>
                                <select name="tipo_pago" class="custom-select">
                                    <option value="anticipo">Anticipo</option>
                                    <option value="liquidacion">Liquidación</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-success mt-3">
                            <i class="fas fa-plus mr-1"></i> Asentar Cobro
                        </button>
                    </form>

                    <form method="POST" action="{{ route('ordenes.confirmarEntrega', $orden->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-dark-modern btn-block py-3" style="font-size:1rem;"
                            onclick="return confirm('¿Confirmar entrega? La orden sera cerrada.')"
                            {{ $saldo > 0 ? 'disabled' : '' }}>
                            <i class="fas fa-handshake mr-2"></i> Confirmar Entrega al Cliente
                        </button>
                    </form>
                </div>
            </div>

        @elseif($orden->estado === 'entregado')
            {{-- ─── PANEL: ENTREGADO / CERRADO ─── --}}
            <div class="card" style="border-left: 4px solid #111827;">
                <div class="card-header" style="background:#111827;">
                    <h5 class="card-title text-white"><i class="fas fa-flag-checkered mr-2"></i>Orden Cerrada</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle" style="font-size:3rem; color:#10b981;"></i>
                        <h4 class="mt-3" style="font-weight:700;">¡Orden Completada!</h4>
                        <p class="text-muted">Esta orden ha sido finalizada y el equipo fue entregado al cliente.</p>
                        @if($orden->fecha_entrega_real)
                            <p class="text-muted" style="font-size:0.85rem;">
                                <i class="fas fa-calendar-check mr-1"></i> Entregado el {{ \Carbon\Carbon::parse($orden->fecha_entrega_real)->format('d M, Y — H:i') }}
                            </p>
                        @endif
                    </div>
                    @if($orden->detallesTecnicos)
                        <div class="p-3 rounded mt-3" style="background:#f8fafc; border:1px solid #e5e7eb;">
                            <strong>Trabajo realizado:</strong>
                            <p class="text-muted mt-2 mb-0">{{ $orden->detallesTecnicos->trabajo_finalizado ?? '—' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- GALERÍA DE EVIDENCIAS --}}
        @if($orden->evidencias->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-images mr-2 text-info"></i>Evidencias ({{ $orden->evidencias->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="evidence-gallery">
                        @foreach($orden->evidencias as $ev)
                            <div class="evidence-thumb">
                                <a href="{{ asset('storage/' . $ev->url_foto) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $ev->url_foto) }}" alt="Evidencia">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- REPUESTOS USADOS --}}
        @if($orden->repuestos->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-tools mr-2 text-secondary"></i>Repuestos / Materiales</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Pieza</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr>
                        </thead>
                        <tbody>
                            @php $totRep = 0; @endphp
                            @foreach($orden->repuestos as $r)
                                @php
                                    $sub = $r->pivot->cantidad * $r->pivot->precio_fijado;
                                    $totRep += $sub;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $r->nombre_pieza ?? $r->nombre ?? 'Pieza' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $r->sku ?? '—' }}</small>
                                    </td>
                                    <td>{{ $r->pivot->cantidad }}</td>
                                    <td>${{ number_format($r->pivot->precio_fijado, 2) }}</td>
                                    <td style="font-weight:700;">${{ number_format($sub, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr style="background:#f9fafb;">
                                <td colspan="3" class="text-right font-weight-bold">Total:</td>
                                <td style="font-weight:800;">${{ number_format($totRep, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- HISTORIAL DE COBROS --}}
        @if($orden->pagos->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-receipt mr-2 text-success"></i>Historial de Cobros</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Monto</th><th>Método</th><th>Tipo</th><th>Fecha</th></tr></thead>
                        <tbody>
                            @foreach($orden->pagos as $p)
                                <tr>
                                    <td style="font-weight:700;">${{ number_format($p->monto, 2) }}</td>
                                    <td class="text-capitalize">{{ $p->metodo_pago }}</td>
                                    <td>
                                        <span class="badge {{ $p->tipo_pago == 'anticipo' ? 'badge-info' : 'badge-success' }}">
                                            {{ ucfirst($p->tipo_pago) }}
                                        </span>
                                    </td>
                                    <td class="text-muted" style="font-size:0.82rem;">{{ \Carbon\Carbon::parse($p->created_at)->format('d M, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- OVERRIDE ADMIN --}}
        @if(Auth::user()->rol === 'admin' && $orden->estado !== 'entregado')
            <div class="card mt-3" style="border: 1px dashed #d1d5db;">
                <div class="card-header" style="background:#fffbeb;">
                    <h6 class="card-title mb-0" style="color:#92400e;">
                        <i class="fas fa-shield-alt mr-2"></i>Anulación Admin — Cambio de Estado de Emergencia
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('ordenes.update', $orden->id) }}" class="d-flex align-items-end gap-2">
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-0 mr-3" style="flex:1;">
                            <select name="estado" class="custom-select">
                                @foreach([
                                    'recibido'   => 'Recibido',
                                    'diagnostico'=> 'Diagnóstico',
                                    'espera'     => 'En Espera',
                                    'rechazado'  => 'Rechazado',
                                    'reparacion' => 'En Reparación',
                                    'para_pzas'  => 'Para Piezas',
                                    'listo'      => 'Listo',
                                    'entregado'  => 'Entregado'
                                ] as $v => $l)
                                    <option value="{{ $v }}" {{ $orden->estado === $v ? 'selected' : '' }}>
                                        {{ $l }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning mb-0" onclick="return confirm('¿Seguro? Esto anulará el flujo normal.')">
                            Aplicar Override
                        </button>
                    </form>
                </div>
            </div>
        @endif

    </div>

    {{-- ===================== COLUMNA DERECHA: Datos persistentes ===================== --}}
    <div class="col-md-4">

        {{-- DATOS DEL CLIENTE --}}
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-user mr-2 text-primary"></i>Cliente</h5></div>
            <div class="card-body">
                <div class="info-row"><span class="info-label">Nombre</span><span class="info-value">{{ $orden->equipo->cliente->nombre ?? '' }} {{ $orden->equipo->cliente->apellido_paterno ?? '' }}</span></div>
                <div class="info-row"><span class="info-label">Teléfono</span><span class="info-value">{{ $orden->equipo->cliente->telefono ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Correo</span><span class="info-value" style="font-size:0.82rem; word-break:break-word;">{{ $orden->equipo->cliente->correo ?? 'N/A' }}</span></div>
            </div>
        </div>

        {{-- DATOS DEL EQUIPO --}}
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-desktop mr-2 text-secondary"></i>Equipo</h5></div>
            <div class="card-body">
                <div class="info-row"><span class="info-label">Tipo</span><span class="info-value text-capitalize">{{ $orden->equipo->tipo ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Marca / Modelo</span><span class="info-value">{{ $orden->equipo->marca ?? '' }} {{ $orden->equipo->modelo ?? '' }}</span></div>
                <div class="info-row"><span class="info-label">N° Serie</span><span class="info-value" style="font-family:monospace; font-size:0.82rem;">{{ $orden->equipo->numero_serie ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Estado Físico</span><span class="info-value">{{ $orden->estado_fisico }}</span></div>
                <div class="info-row"><span class="info-label">Falla</span><span class="info-value">{{ $orden->falla_reportada }}</span></div>
            </div>
        </div>

        {{-- TÉCNICO ASIGNADO --}}
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-user-cog mr-2 text-info"></i>Técnico Asignado</h5></div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width:40px; height:40px; background:#111827; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; flex-shrink:0;">
                        {{ strtoupper(substr($orden->user->nombre ?? 'T', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:600;">{{ $orden->user->nombre ?? 'Sin asignar' }} {{ $orden->user->apellido ?? '' }}</div>
                        <div style="font-size:0.8rem; color:#6b7280;">{{ $orden->user->email ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RESUMEN FINANCIERO --}}
        <div class="card" style="border: 2px solid #e5e7eb !important;">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-calculator mr-2"></i>Resumen Financiero</h5></div>
            <div class="card-body">
                @php
                    $manoObra    = $orden->mano_obra ?? 0;
                    $totalPiezas = $orden->repuestos->sum(fn($r) => $r->pivot->cantidad * $r->pivot->precio_fijado);
                    $montoPiezas = $orden->monto_compra_piezas ?? 0;
                    $totalDebe   = $esReparableOrden ? ($manoObra + $totalPiezas) : $montoPiezas;
                    $totalPagado = $orden->pagos->sum('monto');
                    $saldo       = $totalDebe - $totalPagado;
                @endphp

                @if($esReparableOrden)
                    <div class="info-row"><span class="info-label">Mano de Obra</span><span class="info-value">${{ number_format($manoObra, 2) }}</span></div>
                    <div class="info-row"><span class="info-label">Repuestos</span><span class="info-value">${{ number_format($totalPiezas, 2) }}</span></div>
                    <div class="info-row" style="border-top:1px solid #f3f4f6; padding-top:0.5rem; margin-top:0.5rem;">
                        <span class="info-label">Total a Cobrar</span>
                        <span class="info-value" style="font-size:1.1rem;">${{ number_format($totalDebe, 2) }}</span>
                    </div>
                @else
                    <div class="info-row"><span class="info-label">Tipo</span><span class="info-value">Oferta por piezas</span></div>
                    <div class="info-row"><span class="info-label">Monto Ofrecido</span><span class="info-value">${{ number_format($montoPiezas, 2) }}</span></div>
                    <div class="info-row" style="border-top:1px solid #f3f4f6; padding-top:0.5rem; margin-top:0.5rem;">
                        <span class="info-label">Total del Proceso</span>
                        <span class="info-value" style="font-size:1.1rem;">${{ number_format($totalDebe, 2) }}</span>
                    </div>
                @endif

                <div class="info-row"><span class="info-label">Registrado</span><span class="info-value text-success">${{ number_format($totalPagado, 2) }}</span></div>
                <div class="info-row">
                    <span class="info-label">Saldo</span>
                    <span class="info-value {{ $saldo > 0 ? 'text-danger' : 'text-success' }}" style="font-size:1.1rem; font-weight:800;">
                        {{ $saldo > 0 ? '-' : '' }}${{ number_format(abs($saldo), 2) }}
                        @if($saldo <= 0)
                            <i class="fas fa-check-circle ml-1"></i>
                        @endif
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let repuestosSeleccionados = [];

        const selectEstadoReparable = document.getElementById('es_reparable');
        const bloqueReparable = document.getElementById('bloque-reparable');
        const bloqueNoReparable = document.getElementById('bloque-no-reparable');
        const solicitudCompraPanel = document.getElementById('solicitud-compra-panel');
        const inputManoObra = document.getElementById('mano_obra');
        const inputMontoPiezas = document.getElementById('monto_compra_piezas');

        document.addEventListener('click', function(e) {
            if (e.target.closest('#btn-add-repuesto')) {
                let select = document.getElementById('select-repuesto');
                let opt = select.options[select.selectedIndex];
                let id = select.value;
                if (!id) return;

                let nombre = opt.getAttribute('data-nombre');
                let precio = parseFloat(opt.getAttribute('data-precio'));
                let stock  = parseInt(opt.getAttribute('data-stock'));
                let cantInput = document.getElementById('cant-repuesto');
                let cant   = parseInt(cantInput.value);

                if (cant < 1) return;
                if (cant > stock) {
                    alert('La cantidad excede el stock (' + stock + ').');
                    return;
                }

                let idx = repuestosSeleccionados.findIndex(r => r.id === id);
                if (idx !== -1) {
                    if (repuestosSeleccionados[idx].cant + cant > stock) {
                        alert('Cantidad acumulada excede el stock.');
                        return;
                    }
                    repuestosSeleccionados[idx].cant += cant;
                } else {
                    repuestosSeleccionados.push({ id, nombre, cant, precio });
                }

                renderTabla();
                select.value = '';
                cantInput.value = 1;
            }

            let removeBtn = e.target.closest('.btn-remove-diag');
            if (removeBtn) {
                let index = parseInt(removeBtn.getAttribute('data-index'));
                repuestosSeleccionados.splice(index, 1);
                renderTabla();
            }
        });

        function renderTabla() {
            let tbody = document.querySelector('#tabla-repuestos-diagnosis tbody');
            let container = document.getElementById('hidden-inputs-repuestos');
            if (!tbody || !container) return;

            tbody.innerHTML = '';
            container.innerHTML = '';
            let total = 0;

            repuestosSeleccionados.forEach((r, i) => {
                let sub = r.cant * r.precio;
                total += sub;

                let row = `<tr>
                    <td>${r.nombre}</td>
                    <td>${r.cant}</td>
                    <td>$${r.precio.toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td><b>$${sub.toLocaleString('es-MX', {minimumFractionDigits: 2})}</b></td>
                    <td>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-diag" data-index="${i}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);

                container.insertAdjacentHTML('beforeend', `
                    <input type="hidden" name="repuestos[${i}][id]" value="${r.id}">
                    <input type="hidden" name="repuestos[${i}][cantidad]" value="${r.cant}">
                    <input type="hidden" name="repuestos[${i}][precio]" value="${r.precio}">
                `);
            });

            document.getElementById('total-piezas-diag').textContent =
                '$' + total.toLocaleString('es-MX', {minimumFractionDigits: 2});
        }

        function limpiarRepuestosDiagnostico() {
            repuestosSeleccionados = [];
            renderTabla();

            const selectRepuesto = document.getElementById('select-repuesto');
            const cantRepuesto = document.getElementById('cant-repuesto');

            if (selectRepuesto) selectRepuesto.value = '';
            if (cantRepuesto) cantRepuesto.value = 1;
        }

        function toggleTipoDiagnostico() {
            if (!selectEstadoReparable) return;

            const esReparable = selectEstadoReparable.value === '1';

            if (bloqueReparable) {
                bloqueReparable.style.display = esReparable ? '' : 'none';
            }

            if (bloqueNoReparable) {
                bloqueNoReparable.style.display = esReparable ? 'none' : '';
            }

            if (solicitudCompraPanel) {
                solicitudCompraPanel.style.display = esReparable ? '' : 'none';
            }

            if (esReparable) {
                if (inputMontoPiezas) inputMontoPiezas.value = '';
            } else {
                if (inputManoObra) inputManoObra.value = 0;
                limpiarRepuestosDiagnostico();
            }
        }

        if (selectEstadoReparable) {
            selectEstadoReparable.addEventListener('change', toggleTipoDiagnostico);
            toggleTipoDiagnostico();
        }
    });
</script>
@stop