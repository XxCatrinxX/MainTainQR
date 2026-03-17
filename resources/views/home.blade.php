@extends('adminlte::page')

@section('content')

<div class="row">
    <!-- Órdenes Abiertas -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $totalAbiertas }}</h3>
                <p>Órdenes Abiertas</p>
            </div>
            <div class="icon"><i class="fas fa-folder-open"></i></div>
        </div>
    </div>

    <!-- Órdenes Pendientes -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 class="text-white">{{ $totalPendientes }}</h3>
                <p>Órdenes Pendientes</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
        </div>
    </div>

    <!-- En proceso -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalProceso }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon"><i class="fas fa-sync-alt"></i></div>
        </div>
    </div>

    <!-- Cerradas -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $totalCerradas }}</h3>
                <p>Órdenes Cerradas</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
</div>

<div class="row">

    <!-- GRÁFICA -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title text-bold">Resumen de Órdenes</h3>
            </div>
            <div class="card-body">
                <canvas id="orderChart" style="min-height: 250px;"></canvas>
            </div>
        </div>

        <!-- ÓRDENES RECIENTES -->
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title text-bold">Órdenes Recientes</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Equipo</th>
                            <th>Estado</th>
                            <th>Cliente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ordenesRecientes as $orden)
                        <tr>
                            <td>ORD-{{ $orden->id_orden }}</td>

                            <td>
                                {{ $orden->equipo->tipo_equipo ?? 'N/A' }} 
                                {{ $orden->equipo->marca ?? '' }}
                            </td>

                            <td>
                                @php $estado = strtolower($orden->estado); @endphp

                                @if($estado == 'abierta')
                                    <span class="badge badge-secondary">Abierta</span>

                                @elseif($estado == 'en_diagnostico')
                                    <span class="badge badge-warning">Diagnóstico</span>

                                @elseif($estado == 'en_cotizacion')
                                    <span class="badge badge-info">Cotización</span>

                                @elseif($estado == 'en_proceso')
                                    <span class="badge badge-primary">En proceso</span>

                                @elseif($estado == 'cerrada')
                                    <span class="badge badge-success">Cerrada</span>

                                @elseif($estado == 'cancelada')
                                    <span class="badge badge-danger">Cancelada</span>

                                @else
                                    <span class="badge badge-light">{{ $orden->estado }}</span>
                                @endif
                            </td>

                            <td>
                                {{ $orden->equipo->cliente->nombre ?? 'Sin cliente' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay órdenes registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- LADO DERECHO -->
    <div class="col-md-4">

        <!-- BOTÓN -->
        <div class="mb-3 text-right">
            <a href="{{ route('ordenes.create') }}" class="btn btn-success btn-block shadow">
                <i class="fas fa-plus"></i> Nueva Orden
            </a>
        </div>

        <!-- INFO EXTRA -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-bold">Información</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Panel de control del sistema de mantenimiento. Aquí puedes visualizar el estado actual de las órdenes.
                </p>
            </div>
        </div>

    </div>
</div>

@endsection


@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const abiertas = {{ $chartData['abiertas'] }};
    const cerradas = {{ $chartData['cerradas'] }};

    const ctx = document.getElementById('orderChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['L', 'M', 'M', 'J', 'V', 'S', 'D'],
            datasets: [
                {
                    label: 'Abiertas',
                    data: [abiertas, abiertas, abiertas, abiertas, abiertas, abiertas, abiertas],
                    borderColor: '#007bff',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Cerradas',
                    data: [cerradas, cerradas, cerradas, cerradas, cerradas, cerradas, cerradas],
                    borderColor: '#28a745',
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
@stop