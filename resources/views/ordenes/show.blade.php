@extends('adminlte::page')

@section('title', 'Orden ' . $orden->folio)

@section('css')
    {{-- Estilos centralizados en admin-custom.css --}}

    <style>
    .evidence-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
}

.evidence-thumb {
    width: 100%;
    height: 150px;
    overflow: hidden;
    border-radius: 10px;
    border: 1px solid #ddd;
    background: #f8f9fa;
}

.evidence-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
</style>
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
        <h5 class="card-title">
            <i class="fas fa-stethoscope mr-2 text-warning"></i>
            Paso 2: Diagnóstico Técnico
        </h5>
    </div>
    <div class="card-body">

        <div class="text-center p-4 rounded" style="background: #fffbeb; border: 1px dashed #f59e0b;">
            <div class="mb-3">
                <i class="fas fa-mobile-alt fa-2x text-warning mb-2"></i>
                <div class="spinner-grow spinner-grow-sm text-warning" role="status"></div>
            </div>

            <h6 class="font-weight-bold text-warning mb-1">
                Esperando diagnóstico del técnico
            </h6>

            <p class="small text-muted mb-0">
                El técnico <strong>{{ $orden->user->nombre ?? 'asignado' }}</strong>
                debe realizar el diagnóstico desde la aplicación móvil para continuar con el proceso.
            </p>
        </div>

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

                    {{-- REGISTRO DE COBRO Y ENTREGA --}}
                    @if(in_array(Auth::user()->rol, ['admin', 'recepcionista']))
                    <form method="POST" action="{{ route('ordenes.pago', $orden->id) }}" class="mb-4 pb-3 border-bottom">
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
                    @else
                        <div class="alert alert-info mt-3" style="border-radius:8px;">
                            <i class="fas fa-info-circle mr-2"></i> 
                            Esta orden está en etapa de Entrega y Facturación. Solo Recepcionistas y Administradores pueden registrar pagos y liberar el equipo.
                        </div>
                    @endif
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
        @if($orden->evidencias->count() > 0 || $orden->comentarios_tecnico || $orden->mano_obra || $orden->qr_token)
<div class="card mt-4 shadow-sm border-0">
    
    {{-- HEADER --}}
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-tools mr-2"></i>Detalle del servicio
        </h5>
    </div>

    <div class="card-body">

        {{-- 🔑 TOKEN --}}
        @if($orden->token_rastreo)
            <div class="mb-3">
                <label class="font-weight-bold text-muted">Seguimiento:</label><br>

                @php
                    $link = url('/seguimiento/' . $orden->token_rastreo);
                @endphp

                <a href="{{ $link }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-link mr-1"></i>
                    {{ $link }}
                </a>
            </div>
        @endif

        {{-- 💰 MANO DE OBRA --}}
        @if($orden->mano_obra)
            <div class="mb-3">
                <label class="font-weight-bold text-muted">Costo de mano de obra:</label>
                <div class="alert alert-success py-2 px-3 mb-0">
                    💰 ${{ number_format($orden->mano_obra, 2) }}
                </div>
            </div>
        @endif

        {{-- 📝 COMENTARIOS --}}
        @if($orden->comentarios_tecnico)
            <div class="mb-4">
                <label class="font-weight-bold text-muted">Comentarios del técnico:</label>
                <div class="p-3 border rounded bg-light">
                    {{ $orden->comentarios_tecnico }}
                </div>
            </div>
        @endif

        {{-- 📸 EVIDENCIAS --}}
        @if($orden->evidencias->count() > 0)
            <hr>
            <h6 class="mb-3">
                <i class="fas fa-images text-info mr-2"></i>
                Evidencias ({{ $orden->evidencias->count() }})
            </h6>

            <div class="row">
                @foreach($orden->evidencias as $ev)
                    <div class="col-md-3 col-6 mb-3">
                        <a href="{{ asset('storage/' . $ev->url_foto) }}" target="_blank">
                            <img 
                                src="{{ asset('storage/' . $ev->url_foto) }}" 
                                class="img-fluid rounded shadow-sm evidence-img"
                                style="height: 150px; object-fit: cover; width:100%;"
                            >
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

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
                <div class="info-row"><span class="info-label">Nombre</span><span class="info-value">{{ $orden->cliente->nombre ?? '' }} {{ $orden->cliente->apellido_paterno ?? '' }}</span></div>
                <div class="info-row"><span class="info-label">Teléfono</span><span class="info-value">{{ $orden->cliente->telefono ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Correo</span><span class="info-value" style="font-size:0.82rem; word-break:break-word;">{{ $orden->cliente->correo ?? 'N/A' }}</span></div>
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
            
            const checkboxOfrecer = document.getElementById('ofrecer_compra');
            const bloquePrecio = document.getElementById('bloque-precio-compra');
            
            if (checkboxOfrecer) {
                checkboxOfrecer.addEventListener('change', function() {
                    if (bloquePrecio) bloquePrecio.style.display = this.checked ? 'block' : 'none';
                });
            }
            
            toggleTipoDiagnostico();
            // Trigger checkbox change on load if needed
            if (checkboxOfrecer && checkboxOfrecer.checked && bloquePrecio) {
                bloquePrecio.style.display = 'block';
            }
        }
    });
</script>
@stop