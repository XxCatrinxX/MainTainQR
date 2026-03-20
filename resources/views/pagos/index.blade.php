@extends('adminlte::page')

@section('title', 'Historial de Pagos')

@section('css')
<style>
    body { background-color: #fafafa !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }
    .content-wrapper { background-color: transparent !important; }
    .content-header h1 { font-weight: 700; font-size: 1.5rem; color: #111827; margin-bottom: 0.5rem; }
    
    .card { border: none !important; border-radius: 12px !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important; margin-bottom: 1.5rem; }
    .table-modern { margin: 0; border-collapse: separate; border-spacing: 0; width: 100%; }
    .table-modern th { background-color: #f9fafb !important; color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-top: none; border-bottom: 1px solid #f3f4f6; padding: 1rem 1.5rem; font-weight: 600; }
    .table-modern td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f3f4f6; color: #374151; font-size: 0.95rem; }
    .table-modern tbody tr:last-child td { border-bottom: none; }
    .table-modern tbody tr:hover { background-color: #f8fafc; }
    
    .badge-modern { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; }
    .badge-anticipo { background-color: #e0e7ff; color: #3730a3; }
    .badge-liquidacion { background-color: #dcfce7; color: #166534; }

    .btn-modern { border-radius: 8px !important; font-weight: 500 !important; padding: 0.5rem 1.25rem; transition: all 0.2s ease; border: none; }
    .btn-dark-modern { background-color: #000000 !important; color: #ffffff !important; }
    .btn-dark-modern:hover { background-color: #333333 !important; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important; color: white !important; }
</style>
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
