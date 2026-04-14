@extends('adminlte::page')

@section('title', 'Órdenes de Servicio')

@section('css')
    {{-- Los estilos personalizados ahora están centralizados en admin-custom.css gestionado por Vite --}}
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Órdenes de Servicio</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Gestiona y da seguimiento a los equipos ingresados.</p>
        </div>
        @if(Auth::user()->rol !== 'tecnico')
        <div class="d-flex">
            @if(Auth::user()->rol === 'admin')
                <a href="{{ route('ordenes.archivadas') }}" class="btn btn-outline-secondary mr-2 shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-trash-alt mr-1"></i> Ver Papelera
                </a>
            @endif
            <a href="{{ route('ordenes.create_paso1') }}" class="btn btn-dark-modern shadow-sm">
                <i class="fas fa-plus mr-1"></i> Nueva Orden
            </a>
        </div>
        @endif
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
                    <th>Asignado a</th>
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
                                @if($orden->decision_cliente === 'rechaza')
                                    <span class="badge badge-danger" style="background-color: #f59e0b !important; border:none;">Listo (Rechazado)</span>
                                @elseif($orden->es_reparable === false || $orden->es_reparable === 0)
                                    <span class="badge badge-warning">Listo (No Rep.)</span>
                                @else
                                    <span class="badge badge-success">Listo (Reparado)</span>
                                @endif
                            @elseif($orden->estado == 'entregado')
                                <span class="badge badge-dark">Entregado</span>
                            @elseif($orden->estado == 'para_pzas')
                                <span class="badge badge-info" style="background-color: #10b981 !important; color: white !important;">Para Piezas</span>
                            @elseif($orden->estado == 'rechazado')
                                <span class="badge badge-danger">Rechazado</span>
                            @endif
                        </td>
                        
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 32px; height: 32px; font-size: 0.8rem; font-weight: bold; color: #6b7280;">
                                    {{ strtoupper(substr($orden->user->nombre ?? 'U', 0, 1)) }}
                                </div>
                                <span style="font-weight: 500; color: #4b5563;">{{ $orden->user->nombre ?? 'Sin asignar' }}</span>
                            </div>
                        </td>

                        <td style="color: #6b7280; font-size: 0.9rem;">
                            {{ \Carbon\Carbon::parse($orden->fecha_recepcion)->format('d M, Y') }}
                        </td>

                        <td class="text-right">

    <!-- Ver detalle -->
    @if(Auth::user()->rol !== 'almacenista')
    <a href="{{ route('ordenes.show', $orden->id) }}" 
       class="btn btn-action" 
       title="Ver Detalle">
        <i class="fas fa-eye"></i>
    </a>
    @endif

    <!-- Ver QR -->
    <a href="{{ route('ordenes.qr', $orden->id) }}" 
       class="btn btn-action" 
       title="Ver QR">
        <i class="fas fa-qrcode"></i>
    </a>

    <!-- Eliminar -->
@if(Auth::user()->rol === 'admin')
    <form action="{{ route('ordenes.destroy', $orden->id) }}" method="POST" style="display:inline-block;" 
          onsubmit="return confirm('¿Estás seguro? Esta acción moverá la orden a la papelera.')">
        @csrf
        @method('DELETE')

        <button type="submit" class="btn btn-action text-danger" title="Eliminar Orden">
            <i class="fas fa-trash"></i>
        </button>
    </form>
@endif

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