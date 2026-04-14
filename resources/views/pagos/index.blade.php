@extends('adminlte::page')

@section('title', 'Ingresos y Pagos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Ingresos y Pagos</h1>
    </div>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="border-radius: 8px; font-weight: 500;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<!-- KPIs -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 12px; background: white;">
            <div class="card-body px-4 py-3 d-flex align-items-center">
                <div class="mr-3 p-3 rounded-circle" style="background-color: #ecfccb; color: #4d7c0f;">
                    <i class="fas fa-arrow-down fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-uppercase text-muted font-weight-bold mb-1" style="letter-spacing: 0.05em; font-size: 0.8rem;">Total Ingresos Recibidos</h6>
                    <h3 class="mb-0 font-weight-bold" style="color: #111827;">${{ number_format($total_ingresos, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 12px; background: white;">
            <div class="card-body px-4 py-3 d-flex align-items-center">
                <div class="mr-3 p-3 rounded-circle" style="background-color: #fee2e2; color: #b91c1c;">
                    <i class="fas fa-arrow-up fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-uppercase text-muted font-weight-bold mb-1" style="letter-spacing: 0.05em; font-size: 0.8rem;">Total Pagado por Piezas</h6>
                    <h3 class="mb-0 font-weight-bold" style="color: #111827;">${{ number_format($total_egresos, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<h4 class="mb-3 font-weight-bold" style="color: #374151;">Órdenes Pendientes de Pago</h4>
<div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-top-0">Folio</th>
                    <th class="border-top-0">Cliente</th>
                    <th class="border-top-0">Estado</th>
                    <th class="border-top-0">Calculado</th>
                    <th class="border-top-0 text-danger">Saldo Restante</th>
                    <th class="border-top-0">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordenes_pendientes as $orden)
                    <tr>
                        <td class="align-middle font-weight-bold">{{ $orden->folio }}</td>
                        <td class="align-middle">{{ $orden->equipo->cliente->nombre ?? 'N/A' }}</td>
                        <td class="align-middle text-uppercase">
                            @if($orden->estado == 'listo')
                                @if($orden->decision_cliente === 'rechaza')
                                    <span class="badge badge-danger" style="background-color: #f59e0b !important; color: white !important;">Listo (Rechazado)</span>
                                @elseif($orden->es_reparable === false || $orden->es_reparable === 0)
                                    <span class="badge badge-warning">Listo (No Rep.)</span>
                                @else
                                    <span class="badge badge-success">Listo (Reparado)</span>
                                @endif
                            @elseif($orden->estado == 'para_pzas')
                                <span class="badge badge-info">Para Piezas</span>
                            @else
                                <span class="badge badge-secondary">{{ $orden->estado }}</span>
                            @endif
                        </td>
                        <td class="align-middle">${{ number_format($orden->total_calculado, 2) }}</td>
                        <td class="align-middle font-weight-bold text-danger">${{ number_format($orden->restante, 2) }}</td>
                        <td class="align-middle">
                            <a href="{{ route('ordenes.show', $orden->id) }}" class="btn btn-sm btn-outline-dark" style="border-radius: 6px;">
                                <i class="fas fa-external-link-alt mr-1"></i> Ver Orden
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            No hay órdenes con saldos pendientes por cobrar o pagar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<h4 class="mb-3 font-weight-bold" style="color: #374151;">Últimos Movimientos</h4>
<div class="card overflow-hidden shadow-sm border-0" style="border-radius: 12px;">
    <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-top-0">Folio de Orden</th>
                    <th class="border-top-0">Monto</th>
                    <th class="border-top-0">Canal</th>
                    <th class="border-top-0">Clasificación</th>
                    <th class="border-top-0">Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pagos as $pago)
                    <tr>
                        <td class="align-middle" style="font-family: monospace; font-weight: 600; color: #4b5563;">
                            {{ $pago->orden_servicio->folio ?? 'N/A' }}
                        </td>
                        <td class="align-middle" style="font-weight: 600; color: {{ $pago->tipo_pago == 'pago_cliente' ? '#b91c1c' : '#111827' }};">
                            {{ $pago->tipo_pago == 'pago_cliente' ? '-' : '+' }}${{ number_format($pago->monto, 2) }}
                        </td>
                        <td class="align-middle">
                            <span class="text-capitalize text-muted">
                                <i class="fas {{ $pago->metodo_pago == 'efectivo' ? 'fa-money-bill-wave' : ($pago->metodo_pago == 'tarjeta' ? 'fa-credit-card' : 'fa-university') }} mr-1"></i>
                                {{ $pago->metodo_pago }}
                            </span>
                        </td>
                        <td class="align-middle">
                            @if($pago->tipo_pago == 'anticipo')
                                <span class="badge badge-info text-uppercase">Anticipo</span>
                            @elseif($pago->tipo_pago == 'pago_cliente')
                                <span class="badge badge-danger text-uppercase">Pago a Cliente</span>
                            @else
                                <span class="badge badge-success text-uppercase">Liquidación</span>
                            @endif
                        </td>
                        <td class="align-middle text-muted" style="font-size: 0.85rem;">
                            {{ \Carbon\Carbon::parse($pago->created_at)->format('d M, Y h:i a') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-wallet" style="font-size: 2rem; opacity: 0.5; margin-bottom: 1rem; display: block;"></i>
                            Aún no hay transacciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop

