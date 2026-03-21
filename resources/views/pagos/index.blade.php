@extends('adminlte::page')

@section('title', 'Historial de Pagos')

@section('css')
{{-- Estilos centralizados en app.css --}}
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Ingresos y Pagos</h1>
        <a href="{{ route('pagos.create') }}" class="btn btn-modern btn-dark-modern">
            <i class="fas fa-plus mr-1"></i> Registrar Ingreso
        </a>
    </div>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="border-radius: 8px; font-weight: 500;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Folio de Orden</th>
                    <th>Subtotal Abonado</th>
                    <th>Método de Pago</th>
                    <th>Clasificación</th>
                    <th>Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pagos as $pago)
                    <tr>
                        <td style="font-family: monospace; font-weight: 600; color: #4b5563;">
                            {{ $pago->orden_servicio->folio ?? 'N/A' }}
                        </td>
                        <td style="font-weight: 600; color: #111827;">${{ number_format($pago->monto, 2) }}</td>
                        <td>
                            <span class="text-capitalize text-muted">
                                <i class="fas {{ $pago->metodo_pago == 'efectivo' ? 'fa-money-bill-wave' : ($pago->metodo_pago == 'tarjeta' ? 'fa-credit-card' : 'fa-university') }} mr-1"></i>
                                {{ $pago->metodo_pago }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-modern text-capitalize {{ $pago->tipo_pago == 'anticipo' ? 'badge-anticipo' : 'badge-liquidacion' }}">
                                {{ $pago->tipo_pago }}
                            </span>
                        </td>
                        <td class="text-muted" style="font-size: 0.85rem;">
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
