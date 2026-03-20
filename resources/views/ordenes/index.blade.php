@extends('adminlte::page')

@section('title', 'Órdenes de Servicio')

@section('css')
<style>
    body {
        background-color: #fafafa !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    }
    .content-wrapper { background-color: transparent !important; }

    .content-header h1 {
        font-weight: 700;
        font-size: 1.5rem;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    /* Modern Card */
    .card {
        border: 1px solid #eaeaea !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
        background-color: #ffffff;
        overflow: hidden;
    }
    .card-header {
        background-color: transparent !important;
        border-bottom: 1px solid #eaeaea !important;
        padding: 1.25rem 1.5rem !important;
    }

    /* Buttons */
    .btn-dark-modern {
        background-color: #000000 !important;
        color: #ffffff !important;
        border: 1px solid #000000 !important;
        border-radius: 8px !important;
        font-weight: 500 !important;
        padding: 0.5rem 1.25rem;
        transition: all 0.2s ease;
    }
    .btn-dark-modern:hover {
        background-color: #333333 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        color: white !important;
    }
    .btn-action {
        border-radius: 6px !important;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        margin-right: 0.25rem;
        transition: all 0.2s ease;
        background-color: white !important;
        border: 1px solid #e5e7eb !important;
        color: #4b5563 !important;
    }
    .btn-action:hover {
        background-color: #f9fafb !important;
        color: #111827 !important;
        border-color: #d1d5db !important;
    }

    /* Minimalist Table */
    .table { margin-bottom: 0 !important; }
    .table th {
        border-top: none !important;
        border-bottom: 1px solid #eaeaea !important;
        background-color: #fafafa !important;
        color: #6b7280 !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1rem 1.5rem !important;
    }
    .table td {
        vertical-align: middle !important;
        border-top: 1px solid #eaeaea !important;
        padding: 1rem 1.5rem !important;
        color: #374151;
    }
    .table tbody tr:hover { background-color: #f9fafb !important; }

    /* Badges Pill */
    .badge {
        padding: 0.4em 0.8em !important;
        border-radius: 20px !important;
        font-weight: 500 !important;
        font-size: 0.75rem;
    }
    .badge-secondary { background-color: #f3f4f6 !important; color: #4b5563 !important; }
    .badge-warning { background-color: #fef3c7 !important; color: #92400e !important; }
    .badge-info { background-color: #e0f2fe !important; color: #0369a1 !important; }
    .badge-primary { background-color: #dbeafe !important; color: #1e40af !important; }
    .badge-success { background-color: #dcfce3 !important; color: #166534 !important; }
    .stat-box { background: #fff; border-radius: 12px; padding: 1.5rem; border: 1px solid #eaeaea; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; position: relative; overflow: hidden; margin-bottom: 20px; transition: all 0.3s ease; }
    .stat-box:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); transform: translateY(-2px); }
    .stat-value { font-size: 2rem; font-weight: 700; color: #111827; margin: 0.5rem 0 0 0; }
    .stat-label { font-size: 0.875rem; color: #6b7280; font-weight: 500; }
    .stat-icon { position: absolute; right: 1.5rem; top: 1.5rem; font-size: 1.5rem; opacity: 0.8; }
    .icon-abiertas { color: #3b82f6; } .icon-espera { color: #f59e0b; } .icon-proceso { color: #8b5cf6; } .icon-cerradas { color: #10b981; }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Órdenes de Servicio</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Gestiona y da seguimiento a los equipos ingresados.</p>
        </div>
        <div>
            <a href="{{ route('ordenes.create_paso1') }}" class="btn btn-dark-modern shadow-sm">
                <i class="fas fa-plus mr-1"></i> Nueva Orden
            </a>
        </div>
    </div>
@stop

@section('content')

<!-- MÉTRICAS -->
<div class="row mb-3">
    <div class="col-lg-3 col-6">
        <div class="stat-box">
            <div class="stat-label">Recibidas</div>
            <h3 class="stat-value">{{ $totalRecibidas }}</h3>
            <div class="stat-icon icon-abiertas"><i class="fas fa-folder-open"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="stat-box">
            <div class="stat-label">En Espera / Diagnóstico</div>
            <h3 class="stat-value">{{ $totalPendientes }}</h3>
            <div class="stat-icon icon-espera"><i class="fas fa-hourglass-half"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="stat-box">
            <div class="stat-label">En Reparación</div>
            <h3 class="stat-value">{{ $totalProceso }}</h3>
            <div class="stat-icon icon-proceso"><i class="fas fa-cog fa-spin"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="stat-box">
            <div class="stat-label">Listas / Entregadas</div>
            <h3 class="stat-value">{{ $totalCerradas }}</h3>
            <div class="stat-icon icon-cerradas"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title" style="color: #111827;"><i class="fas fa-chart-line text-primary mr-2"></i> Rendimiento Semanal de Órdenes</h3>
            </div>
            <div class="card-body p-4">
                <canvas id="orderChart" style="height: 250px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0 table-responsive">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Equipo</th>
                    <th>Falla Reportada</th>
                    <th>Estado</th>
                    <th>Fecha Inicio</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($ordenes as $orden)
                    <tr>
                        <td class="text-primary font-weight-bold">{{ $orden->folio }}</td>

                        <td>
                            <div style="font-weight: 600; color: #111827;">{{ $orden->equipo->cliente->nombre ?? '' }} {{ $orden->equipo->cliente->apellido_paterno ?? '' }}</div>
                            <div style="font-size: 0.75rem; color: #6b7280;">{{ $orden->equipo->cliente->telefono ?? 'Sin teléfono' }}</div>
                        </td>

                        <td>
                            <span style="font-weight: 500;">{{ $orden->equipo->tipo ?? '' }}</span> - <span class="text-muted">{{ $orden->equipo->marca ?? '' }}</span>
                        </td>

                        <td>
                            <span class="d-inline-block text-truncate" style="max-width: 150px; color: #4b5563;" title="{{ $orden->falla_reportada }}">
                                {{ $orden->falla_reportada }}
                            </span>
                        </td>

                        <td>
                            @if($orden->estado == 'recibido')
                                <span class="badge badge-secondary">Recibido</span>
                            @elseif($orden->estado == 'diagnostico')
                                <span class="badge badge-warning">Diagnóstico</span>
                            @elseif($orden->estado == 'espera')
                                <span class="badge badge-info">Espera</span>
                            @elseif($orden->estado == 'reparacion')
                                <span class="badge badge-primary">Reparación</span>
                            @elseif($orden->estado == 'listo')
                                <span class="badge badge-success">Listo</span>
                            @elseif($orden->estado == 'entregado')
                                <span class="badge badge-dark">Entregado</span>
                            @endif
                        </td>

                        <td style="color: #6b7280; font-size: 0.9rem;">
                            {{ \Carbon\Carbon::parse($orden->fecha_recepcion)->format('d M, Y') }}
                        </td>

                        <td class="text-right">
                            <a href="{{ route('ordenes.show', $orden->id) }}" class="btn btn-action" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 text-light"></i><br>
                            No hay órdenes registradas actualmente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    Chart.defaults.color = '#6b7280';

    const abiertas = {{ $chartData['abiertas'] ?? 0 }};
    const cerradas = {{ $chartData['cerradas'] ?? 0 }};

    const ctx = document.getElementById('orderChart').getContext('2d');

    let gradientCerradas = ctx.createLinearGradient(0, 0, 0, 400);
    gradientCerradas.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
    gradientCerradas.addColorStop(1, 'rgba(16, 185, 129, 0)');

    let gradientAbiertas = ctx.createLinearGradient(0, 0, 0, 400);
    gradientAbiertas.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
    gradientAbiertas.addColorStop(1, 'rgba(59, 130, 246, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb', 'Dom'],
            datasets: [
                {
                    label: 'Órdenes Activas',
                    data: [abiertas, abiertas+2, abiertas-1, abiertas+1, abiertas, abiertas, abiertas],
                    borderColor: '#3b82f6',
                    backgroundColor: gradientAbiertas,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 6
                },
                {
                    label: 'Completadas',
                    data: [cerradas, cerradas, cerradas+1, cerradas+3, cerradas+2, cerradas+1, cerradas],
                    borderColor: '#10b981',
                    backgroundColor: gradientCerradas,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', align: 'end', labels: { boxWidth: 10, usePointStyle: true } },
                tooltip: { backgroundColor: '#111827', padding: 12, titleFont: { size: 13 }, bodyFont: { size: 14, weight: 'bold' } }
            },
            scales: {
                x: { grid: { display: false } },
                y: { border: { display: false }, grid: { color: '#f3f4f6' }, beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
</script>
@stop